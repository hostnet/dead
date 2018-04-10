<?php 
class GitFileTreeFactoryTest extends \PHPUnit\Framework\TestCase
{
	public function testScan() {
		$factory = new GitFileTreeFactory();
		$factory->scan('.');
	}
}