<?php
require_once "AbstractTreeFactory.php";
require_once "elements/DynamicAnalysis.php";
require_once "elements/FileChange.php";
require_once "elements/Versioning.php";

/**
 * @author Hidde Boomsma <hidde@hostnet.nl>
 * @subpackage tree
 * @copyright Hostnet B.V.
 * @since 2012.01.31 14:03
 * @link http://www.php.net/manual/en/book.pdo.php
 */
class PDOTreeMapFactory extends AbstractTreeFactory
{

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
    
    private $query = "SELECT * FROM %s WHERE file REGEXP %s";

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
    public function query($path)
    {        
        $path = $this->db->quote("^$path/[^/]+$");
        $query = sprintf($this->query, $this->table,$path);
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
        $fileCount = empty($row["file_count"]) ? 0 : $row["file_count"];
        $deadCount = empty($row["dead_count"]) ? 0 : $row["dead_count"];
        $firstHit = empty($row["first_hit"]) ? null
                : new DateTime($row["first_hit"]);
        $lastHit = empty($row["last_hit"]) ? null
                : new DateTime($row["last_hit"]);
        $changedAt = empty($row["changed_at"]) ? null
                : new DateTime($row["changed_at"]);

        $node = new Node($row['file']);

        $version = new Versioning(array(new Commit("", "", $changedAt, "")), 1);
        $node->addElement($version);

        $analysis = new DynamicAnalysis($count, $firstHit, $lastHit,$fileCount,$deadCount);
        $node->addElement($analysis);

        return $node;
    }

}
