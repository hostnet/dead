<?php
/**
 * @copyright 2018 Hostnet B.V.
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
     * @var array[int]Node
     */
    private $leaves = [];

    private $query = "SELECT * FROM %s WHERE function REGEXP %s";

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
     * @param $path string
     * @param $extension string
     * @return void
     */
    public function query($path)
    {
        $regex     = strpos($path, '.php') ? "^$path::.*" : "^$path/[^/:{2}]+$";
        $query     = sprintf($this->query, $this->table, $this->db->quote($regex));
        $statement = $this->db->query($query);
        $statement->execute();

        while (($row = $statement->fetch()) !== false) {
            $this->leaves[] = $this->parseRow($row);
        }
        $statement = null;
    }

    /**
     * @param array $row
     * @return Node
     */
    protected function parseRow(array &$row)
    {
        $count          = empty($row["count"]) ? 0 : $row["count"];
        $function_count = empty($row["function_count"]) ? 0 : $row["function_count"];
        $dead_count     = empty($row["dead_count"]) ? 0 : $row["dead_count"];
        $first_hit      = empty($row["first_hit"]) ? null : new DateTime($row["first_hit"]);
        $last_hit       = empty($row["last_hit"]) ? null : new DateTime($row["last_hit"]);
        $changed_at     = empty($row["changed_at"]) ? null : new DateTime($row["changed_at"]);

        $node = new Node($row['function']);

        $version = new Versioning([new Commit("", "", $changed_at, "")], 1);
        $node->addElement($version);

        $analysis = new DynamicAnalysis($count, $first_hit, $last_hit, $function_count, $dead_count);
        $node->addElement($analysis);

        return $node;
    }
}
