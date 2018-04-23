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

    const ALL_FILES                    = true;
    const ONLY_EXISTING_FILES          = false;
    const ALL_FILES_QUERY              = "SELECT * FROM %s";
    const EXISTING_FILES_QUERY         = "SELECT * FROM %s WHERE deleted_at IS NULL";
    const ALL_FUNCTIONS_QUERY          = "SELECT * FROM %s WHERE function_name IS LIKE \"%s%\"";
    const ALL_EXISTING_FUNCTIONS_QUERY = "SELECT * FROM %s WHERE function_name IS LIKE \"%s%\" AND deleted_at is NULL";

    private $table_files;
    private $table_functions;
    /**
     * The database connection
     * @var PDO
     */
    private $db;

    /**
     *
     * @var Node[]
     */
    private $leaves;

    public function __construct(PDO $db)
    {
        $this->table_files     = $settings = Settings::instance()->getOption("table");
        $this->table_functions = $this->table_files."_functions";
        $this->db              = $db;
        $this->leaves          = [];
    }

    /**
     * @return array[int]Node
     * @see TreeFactoryInterface::getLeaves()
     */
    public function &produceList()
    {
        return $this->leaves;
    }

    public function setTableFiles($table_files)
    {
        $this->table_files = "`$table_files`";
    }

    /**
     *
     * @param bool $all
     * @return void
     */
    public function query($all = self::ONLY_EXISTING_FILES)
    {
        $query     = $all ? self::ALL_FILES_QUERY : self::EXISTING_FILES_QUERY;
        $query     = sprintf($query, $this->table_files);
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
