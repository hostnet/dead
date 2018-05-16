<?php
/**
 * @copyright 2014-2018 Hostnet B.V.
 */
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @covers \FileTreeFactory
 */
class FileTreeFactoryTest extends TestCase
{
    public function testScan()
    {
        $this->markTestSkipped();
        $factory = new FileTreeFactory();
        $factory->scan('.');
    }
}
