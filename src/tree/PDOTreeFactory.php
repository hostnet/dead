<?php

/**
 * @author Hidde Boomsma <hidde@hostnet.nl>
 * @subpackage tree
 * @copyright Hostnet B.V.
 * @since 2012.01.22 10:56
 * @link http://www.php.net/manual/en/book.pdo.php
 */
class PDOTreeFactory extends AbstractTreeFactory
{
  
    const ALL = true;
    const ONLY_EXISTING = false;

    /**
     * The database connection
     * @var PDO
     */
    private $db;

    /**
     * 
     * @var array[int]Node
     */
    private $leaves = array();

    private $query_all = "SELECT * FROM %s";
    private $query = "SELECT * FROM %s WHERE deleted_at IS NULL";

    public function __construct(PDO $db)
    {
      $this->table = $settings = Settings::instance()->getOption("table");
        $this->db = $db;
        
    }

    /**
     * @return array[int]Node
     * @see ITreeFactory::getLeaves()
     */
    public function &produceList()
    {
        return $this->leaves;
    }

    public function setTable($table)
    {
        $this->table = "`$table`";
    }

    /**
     *
     * @param $path string       	
     * @param $extension string       	
     * @return void
     */
    public function query($all = self::ONLY_EXISTING)
    {
        if ($all === self::ALL) {
            $query = $this->query_all;
        } else {
            $query = $this->query;
        }
        $query = sprintf($query, $this->table);
        $statement = $this->db->query($query);
        $statement->execute();

        while (($row = $statement->fetch()) !== false) {
            $this->leaves[] = $this->parseRow($row);
        }
        $statement = null;
    }

    /**
     * 
     * @param array $row
     * @return Node
     */
    protected function parseRow(array &$row)
    {
        $count = empty($row["count"]) ? 0 : $row["count"];
        $firstHit = empty($row["first_hit"]) ? null
                : new DateTime($row["first_hit"]);
        $lastHit = empty($row["last_hit"]) ? null
                : new DateTime($row["last_hit"]);
        $addedAt = empty($row["added_at"]) ? null
                : new DateTime($row["added_at"]);
        $deletedAt = empty($row["deleted_at"]) ? null
                : new DateTime($row["deleted_at"]);
        $changedAt = empty($row["changed_at"]) ? null
                : new DateTime($row["changed_at"]);

        $node = new Node($row['file']);
        
        
        $version = new Versioning(array(new Commit("", "", $changedAt, "")),1);
        $node->addElement($version);
        
        
        $analysis = new DynamicAnalysis($count, $firstHit, $lastHit);
        $node->addElement($analysis);
        
        $file = new FileChange($addedAt, $deletedAt);
        $node->addElement($file);
        return $node;
    }

}
