<?php
require_once "AbstractPDOTask.php";
require_once "tree/FileTreeFactory.php";
require_once "tree/PDOTreeFactory.php";
require_once "visitor/SubversionVisitor.php";
require_once "visitor/GitVisitor.php";
require_once "visitor/PrimeVisitor.php";
require_once "common/Settings.php";

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

    //Make sure the path ends with a separatpr (/ on linux, mac and unix)
    if(substr($path, -1, 1) != DIRECTORY_SEPARATOR) {
      $path .= DIRECTORY_SEPARATOR;
    }

    $this->path = $path;
    $this->vcs = $settings->getCommand()->getOption("vcs");
  }

  /**
   *
   * @param $list array[]Node
   * @return array[string]PrimeData
   */

  private function getPrimeData(array $list)
  {
    // Create PrimeVisitor for fetching data
    $visitor = new PrimeVisitor();

    foreach(array_keys($list) as $key) {
      $list[$key]->accept($visitor);
    }
    return $visitor->getPrimeData();
  }

  private function getFileNodes()
  {
    // Read all file names from disk
    $factory = new FileTreeFactory();
    $factory->scan($this->path);
    return $factory->produceList();
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
    $file = $this->getFileNodes();
    echo "read all files from disk into list\n";

    if($this->vcs == self::SVN) {
      $visitor = new SubversionVisitor($this->path);
      $this->addVersioning($file, $visitor);
      echo "added versioning info from svn to disk file list\n";
    } elseif($this->vcs == self::GIT) {
      $visitor = new GitVisitor($this->path);
      $this->addVersioning($file, $visitor);
      echo "added versioning info from git to disk file list\n";
    } elseif($this->vcs == self::NONE) {
      echo "No versioning info added (no vcs specified)\n";
    }

    $file = $this->getPrimeData($file);
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
        "UPDATE $table SET deleted_at = NOW() WHERE deleted_at IS NULL AND file IN (\"$values\") AND file LIKE \"$this->path%\"";
      $this->getDb()->exec($query);
    }
  }

}
