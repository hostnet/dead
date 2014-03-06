<?php

class PrimeTask extends AbstractPDOTask
{
  // commandline or config
  private $path;
  private $vcs;

  const NONE = 'none';
  const SVN = 'svn';
  const GIT = 'git';

  public function __construct()
  {
    parent::__construct();
    $settings = Settings::instance();
    
    $path = $settings->getCommand()->getArgument("path");
    $path = $this->fixTrailingDirectorySeperator($path);

    $prefix = $settings->getCommand()->getOption("prefix");
    $prefix = $this->fixTrailingDirectorySeperator($prefix);
    
    $this->path   = $path;
    $this->prefix = $prefix;
    $this->vcs    = $settings->getCommand()->getOption("vcs");
  }

  /**
   * Make sure the path ends with a separator (/ on linux, mac and unix)
   * @param $path string the path that sould be fixed
   * @return string
   */
  private function fixTrailingDirectorySeperator($path) {
      if(substr($path, -1, 1) != DIRECTORY_SEPARATOR) {
          $path .= DIRECTORY_SEPARATOR;
      }
      return $path;
  }
  
  /**
   *
   * @param $list array[]Node
   * @return array[string]PrimeData
   */
  private function getPrimeData(array $list, $prefix = null)
  {
    // Create PrimeVisitor for fetching data
    $visitor = new PrimeVisitor();
    $visitor->setPrefix($prefix);

    foreach(array_keys($list) as $key) {
      $list[$key]->accept($visitor);
    }
    return $visitor->getPrimeData();
  }

  private function &addVersioning(&$list, INodeElementVisitor $visitor)
  {
    foreach(array_keys($list) as $key) {
      $list[$key]->accept($visitor);
    }
    return $list;
  }

  private function getDbNodes()
  {
    // Read all files from the databse
    $factory = new PDOTreeFactory($this->getDb());
    $factory->query(PDOTreeFactory::ALL);
    $list = $factory->produceList();
    return $list;

  }

  public function run()
  {
    if($this->vcs == self::SVN) {
      /*
       * SVN
       */
      $file = (new FileTreeFactory())->scan($this->path)->produceList();
      echo "read all files from disk into list\n";
      $visitor = new SubversionVisitor($this->path);
      $this->addVersioning($file, $visitor);
      echo "added versioning info from svn to disk file list\n";
    } elseif($this->vcs == self::GIT) {
      /*
       * GIT
       */
      $file = (new GitFileTreeFactory())->scan($this->path)->produceList();
      echo "read all files from disk into list\n";
      $visitor = new GitVisitor($this->path);
      $this->addVersioning($file, $visitor);
      echo "added versioning info from git to disk file list\n";
    } elseif($this->vcs == self::NONE) {
      /*
       * NONE
       */
      $file = (new FileTreeFactory())->scan($this->path)->produceList();
      echo "read all files from disk into list\n";
      echo "No versioning info added (no vcs specified)\n";
    }

    $file = $this->getPrimeData($file, $this->prefix);
    echo "parsed all disk data into prime data\n";
   
    $db = $this->getDbNodes();
    echo "read all entries from database into list\n";
    $db = $this->getPrimeData($db);
    echo "parsed all database data into prime data\n";

    // Find new and deleted files
    $new = array_diff_key($file, $db);
    $removed = array_diff_key($db, $file);
   
    // Remove them from the original sets
    $db = array_intersect_key($db, $file);
    $file = array_intersect_key($file, $db);

    // Search for differences form local files
    // data comapred to the database
    $diff = array_diff_assoc($file, $db);
   
    // Commit all data to the database
    $this->getDb()->beginTransaction();
    $this->insertNew($new);
    $this->updateDead($removed);
    $this->updateChanged($diff, $db);
    $this->getDb()->commit();
  }

  private function updateChanged(array $diff, array $db)
  {
    if(count($diff) > 0) {
      $table = $this->getTable();
      $sql = "";
      $query =
        "UPDATE $table SET deleted_at = NULL, last_hit=last_hit, changed_at = %s /* was: %s */ WHERE file = \"%s\";\n";
      foreach($diff as $file => $data) {
        $changedAt = $data->getSQLChangedAt();
        $currentChangedAt = $db[$file]->getSQLChangedAt();
        $sql .= sprintf($query, $changedAt, $currentChangedAt, $file);
      }

      $sql;
      $this->getDb()->exec($sql);
    }
  }

  private function insertNew(array $new)
  {
    $table = $this->getTable();
    $db = $this->getDb();
    $new_files = array_chunk($new, 1000, true);
    $batch = 0;
    foreach($new_files as $new) {
      $values = "";
      foreach($new as $file => $data) {
        /*
         * @var $data PrimeData
         */
        $safeFile = $db->quote($file);
        $changedAt = $data->getSQLChangedAt();
        $values .= "($safeFile,NOW(),$changedAt),\n";
      }
      $values = substr($values, 0, -2);

      $query =
        "INSERT INTO $table (file,added_at,changed_at) VALUES\n$values";
      $db->exec($query);
    }

  }

  private function updateDead($removed)
  {
    if(count($removed)) {
      $table = $this->getTable();
      $values = implode("\",\"", array_keys($removed));
      $query =
        "UPDATE $table SET deleted_at = NOW() WHERE deleted_at IS NULL AND file IN (\"$values\")";
      $this->getDb()->exec($query);
    }
  }

}
