<?php
/**
 * @author Hidde Boomsma <hidde@hostnet.nl>
 * @subpackage tree
 * @copyright 2018 Hostnet B.V.
 * @since 2012.01.31 14:03
 * @link http://www.php.net/manual/en/book.pdo.php
 */
declare(strict_types=1);

class PdoTreeMapFactory extends AbstractTreeFactoryInterface
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
        $this->db    = $db;
    }

    /**
     * @return array[int]Node
     * @see TreeFactoryInterface::getLeaves()
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
        $path      = $this->db->quote("^$path/[^/]+$");
        $query     = sprintf($this->query, $this->table, $path);
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
        $count      = empty($row["count"]) ? 0 : $row["count"];
        $file_count = empty($row["file_count"]) ? 0 : $row["file_count"];
        $dead_count = empty($row["dead_count"]) ? 0 : $row["dead_count"];
        $first_hit  = empty($row["first_hit"]) ? null
            : new DateTime($row["first_hit"]);
        $last_hit   = empty($row["last_hit"]) ? null
            : new DateTime($row["last_hit"]);
        $changed_at = empty($row["changed_at"]) ? null
            : new DateTime($row["changed_at"]);

        $node = new Node($row['file']);

        $version = new Versioning(array(new Commit("", "", $changed_at, "")), 1);
        $node->addElement($version);

        $analysis = new DynamicAnalysis($count, $first_hit, $last_hit, $file_count, $dead_count);
        $node->addElement($analysis);

        return $node;
    }
}
