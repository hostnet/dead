<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

class TreeTask extends AbstractPdoTaskInterface
{
    private $table;
    private $query =
        <<<EOQ
    CREATE TABLE IF NOT EXISTS %s (
        function varchar(700) BINARY NOT NULL,
        PRIMARY KEY  (function)
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
        if ($table == "") {
            $table = $this->getTable() . "_tree";
        }
        $this->table = $table;
    }

    public function run()
    {
        $factory = new PdoTreeFactory($this->getDb());
        $factory->query();
        $tree = $factory->produceTree();

        $visitor = new PdoCacheTreeVisitor();
        $tree->acceptDepthFirst($visitor);

        $columns = $this->dataToColumns($visitor->getData());
        $values  = $this->dataToValues($visitor->getData());

        $sql =
            sprintf($this->query, $this->table, $this->table, $this->table, $columns, $values);
        $db  = $this->getDb();
        $db->exec($sql);
    }

    /**
     * @param array $data
     * @return string
     */

    private function dataToColumns(array $data)
    {
        if (count($data) > 0) {
            $data = implode(",", array_keys(reset($data)));
        } else {
            $data = "";
        }

        return $data;
    }

    /**
     * @param array $data
     * @return string
     */

    private function dataToValues(array $data)
    {
        foreach ($data as &$row) {
            foreach ($row as &$field) {
                $field = $this->transformAndEscapeField($field);
            }
            $row = "(" . implode(",", $row) . ")";
        }
        $data = implode(",\n", $data);

        return $data;
    }
}
