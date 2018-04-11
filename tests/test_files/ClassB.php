<?php
/**
 * @copyright 2018 Hostnet B.V.
 */
declare(strict_types=1);

class ClassB
{
    public function __construct()
    {
    }

    private function test3()
    {
        function inTest3()
        {
        }
    }
}
