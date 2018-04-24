<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

class BenchTaskInterface implements TaskInterface
{
    private function addTable($factory, $table)
    {
        $GLOBALS['time_start'];

        $factory->setTable($table);
        try {
            $factory->query();
        } catch (\Throwable $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
        $factory->produceTree();

        $time  = sprintf("%5.2f", microtime(true) - $GLOBALS['time_start']);
        $mem   = sprintf("%4.1f", (memory_get_usage()) / 1024 / 1024);
        $count = sprintf("%10d", $factory->countLeaves());
        echo "$count $mem $time" . PHP_EOL;
    }

    public function run()
    {
        $factory = new PdoTreeFactory($this->getDb());
        self::addTable($factory, "none");
        self::addTable($factory, "ontrack");
        self::addTable($factory, "my2");
        self::addTable($factory, "hft3");
        self::addTable($factory, "hft2");
        self::addTable($factory, "aurora");
        self::addTable($factory, "ontrack");
        self::addTable($factory, "my2");
        self::addTable($factory, "hft3");
        self::addTable($factory, "hft2");
        self::addTable($factory, "aurora");
        self::addTable($factory, "ontrack");
        self::addTable($factory, "my2");
        self::addTable($factory, "hft3");
        self::addTable($factory, "hft2");
        self::addTable($factory, "aurora");
        self::addTable($factory, "ontrack");
        self::addTable($factory, "my2");
        self::addTable($factory, "hft3");
        self::addTable($factory, "hft2");
        self::addTable($factory, "aurora");
    }
}
