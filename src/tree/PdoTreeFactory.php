<?php
/**
 * @author Hidde Boomsma <hidde@hostnet.nl>
 * @subpackage tree
 * @copyright 2018 Hostnet B.V.
 * @since 2012.01.22 10:56
 * @link http://www.php.net/manual/en/book.pdo.php
 */
declare(strict_types=1);

class PdoTreeFactory extends AbstractTreeFactoryInterface
{

    const ALL           = true;
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
    private $query     = "SELECT * FROM %s WHERE deleted_at IS NULL";

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
    public function query($all = self::ONLY_EXISTING)
    {
        if ($all === self::ALL) {
            $query = $this->query_all;
        } else {
            $query = $this->query;
        }
        $query     = sprintf($query, $this->table);
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
        $first_hit  = empty($row["first_hit"]) ? null
            : new DateTime($row["first_hit"]);
        $last_hit   = empty($row["last_hit"]) ? null
            : new DateTime($row["last_hit"]);
        $added_at   = empty($row["added_at"]) ? null
            : new DateTime($row["added_at"]);
        $deleted_at = empty($row["deleted_at"]) ? null
            : new DateTime($row["deleted_at"]);
        $changed_at = empty($row["changed_at"]) ? null
            : new DateTime($row["changed_at"]);

        $node = new Node($row['file']);


        $version = new Versioning(array(new Commit("", "", $changed_at, "")), 1);
        $node->addElement($version);


        $analysis = new DynamicAnalysis($count, $first_hit, $last_hit);
        $node->addElement($analysis);

        $file = new FileChange($added_at, $deleted_at);
        $node->addElement($file);

        return $node;
    }
}
