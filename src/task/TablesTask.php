<?php

class TablesTask extends AbstractPDOTask
{
  public function run()
  {
    $sql = 'SHOW TABLES';
    foreach($this->getDb()->query($sql) as $table) {
      $table = $table[0];
      if(substr($table,-5) !== '_tree') {
        echo "$table\n";
      }
    }
  }
}
