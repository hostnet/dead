<?php
/**
 * @copyright 2014-2018 Hostnet B.V.
 */
declare(strict_types=1);

class TablesTask extends AbstractPdoTaskInterface
{
    public function run()
    {
        $sql = 'SHOW TABLES';
        foreach ($this->getDb()->query($sql) as $table) {
            $table = $table[0];
            if (substr($table, -5) !== '_tree') {
                echo "$table\n";
            }
        }
    }
}
