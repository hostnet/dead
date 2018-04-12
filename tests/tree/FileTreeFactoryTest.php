<?php
/**
 * @copyright 2018 Hostnet B.V.
 */

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

class FileTreeFactoryTest extends TestCase
{

    public function testScan()
    {
        $factory = new FileTreeFactory();
        $factory->scan('.');
    }
}
