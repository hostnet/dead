<?php

class TreeFactoryTest extends PHPUnit_Framework_TestCase
{

    public function testScan()
    {
        $factory = new FileTreeFactory();
        $factory->scan('.');
    }
}