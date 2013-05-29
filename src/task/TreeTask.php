<?php
require_once "AbstractPDOTask.php";
require_once "tree/PDOTreeFactory.php";
require_once "visitor/PDOCacheTreeVisitor.php";
require_once "common/Settings.php";

class TreeTask extends AbstractPDOTask
{

  private $table;
  private $query =
    <<<EOQ
    CREATE TABLE IF NOT EXISTS %s (
        file varchar(255) BINARY NOT NULL,
        count bigint(20) NOT NULL,
        file_count int(11) NOT NULL,
        dead_count int(11) NOT NULL,
        first_hit timestamp NULL default NULL,
        last_hit timestamp NULL default NULL on update CURRENT_TIMESTAMP,
        changed_at timestamp NULL default NULL,
        PRIMARY KEY  (file)
    );
    TRUNCATE %s;
    INSERT INTO `%s` (%s) VALUES %s;
EOQ;

  public function __construct()
  {
    parent::__construct();
    $settings = Settings::instance();

    //Load table from settings, if not given use the
    //Source table name and append _tree
    $table = $settings->getCommand()->getOption("table");
    if($table == "") {
      $table = $this->getTable() . "_tree";
    }
    $this->table = $table;
  }

  public function run()
  {
    $factory = new PDOTreeFactory($this->getDb());
    $factory->query();
    $tree = $factory->produceTree();

    $visitor = new PDOCacheTreeVisitor();
    $tree->acceptDepthFirst($visitor);

    $columns = $this->dataToColumns($visitor->getData());
    $values = $this->dataToValues($visitor->getData());

    $sql =
      sprintf($this->query, $this->table, $this->table, $this->table, $columns, $values);
    $db = $this->getDb();
    $db->exec($sql);
  }

  /**
   *
   * @param array $data
   * @return string
   */

  private function dataToColumns(array $data)
  {
    if(count($data) > 0) {
      $data = implode(",", array_keys(reset($data)));
    } else {
      $data = "";
    }
    return $data;
  }

  /**
   *
   * @param array $data
   * @return string
   */

  private function dataToValues(array $data)
  {

    foreach($data as &$row) {
      foreach($row as &$field) {
        $field = $this->transformAndEscapeField($field);
      }
      $row = "(" . implode(",", $row) . ")";
    }
    $data = implode(",\n", $data);

    return $data;
  }
}
