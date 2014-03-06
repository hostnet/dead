<?php 
class GitFileTreeFactoryTest extends PHPUnit_Framework_TestCase
{
	public function testScan() {
		$factory = new GitFileTreeFactory();
		$factory->scan('.');
	}
}