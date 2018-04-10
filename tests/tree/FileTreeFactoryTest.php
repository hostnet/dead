<?php

class TreeFactoryTest extends \PHPUnit\Framework\TestCase
{

    public function testScan()
    {
        $factory = new FileTreeFactory();
        $factory->scan('.');
    }
}