<?php

use PHPUnit\Framework\TestCase;

class GitFileTreeFactoryTest extends TestCase
{
    public function testScan()
    {
	    $factory = new GitFileTreeFactory();
	    $factory->scan('.');
	}
}
