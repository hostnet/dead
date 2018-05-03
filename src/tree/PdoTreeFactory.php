<?php
/**
 * @copyright 2018 Hostnet B.V.
 * @link http://www.php.net/manual/en/book.pdo.php
 */
declare(strict_types=1);

class PdoTreeFactory extends AbstractTreeFactoryInterface
{
    const FUNCTIONS_ALL            = true;
    const FUNCTIONS_EXISTING       = false;
    const FUNCTIONS_QUERY_ALL      = 'SELECT * FROM %s';
    const FUNCTIONS_QUERY_EXISTING = 'SELECT * FROM %s WHERE deleted_at IS NULL;';

    private $table_functions;
    /**
     * The database connection
     * @var PDO
     */
    private $db;

    /**
     * @var FileFunction[]
     */
    private $leaves;

    public function __construct(PDO $db)
    {
        $this->table_functions = Settings::instance()->getOption('table') . '_functions';
        $this->db              = $db;
        $this->leaves          = [];
    }

    /**
     * @return FileFunction[]
     * @see TreeFactoryInterface::getLeaves()
     */
    public function &produceList()
    {
        return $this->leaves;
    }

    /**
     * @param bool $all
     * @return void
     */
    public function query($all = self::FUNCTIONS_EXISTING)
    {
        $query     = $all ? self::FUNCTIONS_QUERY_ALL : self::FUNCTIONS_QUERY_EXISTING;
        $query     = sprintf($query, $this->table_functions);
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
    protected function parseRow(array &$row): Node
    {
        return new Node($row['function']);
    }
}
