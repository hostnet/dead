<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

class TreeFactoryTest extends TestCase
{

    public function testScan()
    {
        $factory = new FileTreeFactory();
        $factory->scan('.');
    }
}
