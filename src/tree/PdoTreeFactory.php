<?php
/**
 * @copyright 2018 Hostnet B.V.
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
     * @var Node[]
     */
    private $leaves;

    public function __construct(PDO $db)
    {
        $this->table_files     = $settings = Settings::instance()->getOption("table");
        $this->table_functions = $this->table_files . "_functions";
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
            $functions      = $this->queryFunctions($row["file"]);
            $this->leaves[] = $this->parseRow($row, $functions);
        }
        $statement = null;
    }

    /**
     * @param string $file_path
     * @return array[]
     */
    protected function queryFunctions(string $file_path): array
    {
        $query     = "SELECT * FROM $this->table_functions WHERE function LIKE '%$file_path%';";
        $statement = $this->db->query($query);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * @param array $row
     * @param array $functions
     * @return Node
     */
    protected function parseRow(array &$row, array $functions): Node
    {
        $count      = empty($row["count"]) ? 0 : $row["count"];
        $first_hit  = empty($row["first_hit"]) ? null : new DateTime($row["first_hit"]);
        $last_hit   = empty($row["last_hit"]) ? null : new DateTime($row["last_hit"]);
        $added_at   = empty($row["added_at"]) ? null : new DateTime($row["added_at"]);
        $deleted_at = empty($row["deleted_at"]) ? null : new DateTime($row["deleted_at"]);
        $changed_at = empty($row["changed_at"]) ? null : new DateTime($row["changed_at"]);
        $version    = new Versioning([new Commit("", "", $changed_at, "")], 1);
        $analysis   = new DynamicAnalysis($count, $first_hit, $last_hit);
        $file       = new FileChange($added_at, $deleted_at);

        $node = new Node($row['file']);
        $node->addElement($version);
        $node->addElement($analysis);
        $node->addElement($file);
        foreach ($functions as $function) {
            $node->addElement(new FileFunction($function["function"]));
        }

        return $node;
    }
}
