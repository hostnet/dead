<?php
/**
 * @copyright 2018 Hostnet B.V.
 */
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class GitFileTreeFactoryTest extends TestCase
{
    public function testScan()
    {
        $this->markTestSkipped();
        $factory = new GitFileTreeFactory();
        $factory->scan('.');
    }
}
