<?php
require_once "ITask.php";
require_once "tree/PDOTreeFactory.php";

class BenchTask implements ITask {
	private function addTable($factory, $table) {
		global $time_start;
		
		$factory->setTable ( $table );
		try {
			$factory->query ();
		} catch (Exception $e) {
		}
		$factory->produceTree ();
		
		$time =  sprintf ( "%5.2f", microtime ( true ) - $time_start );
		$mem =  sprintf ( "%4.1f", ( memory_get_usage () ) / 1024 / 1024);
		$count= sprintf( "%10d",$factory->countLeaves ());
		echo "$count $mem $time" . PHP_EOL;
		
	}
	
	public function run() {
		
		
		$factory = new PDOTreeFactory ($this->getDb());
		self::addTable($factory, "none");
		self::addTable($factory, "ontrack");
		self::addTable($factory, "my2");
		self::addTable($factory, "hft3");
		self::addTable($factory, "hft2");
		self::addTable($factory, "aurora");
		self::addTable($factory, "ontrack");
		self::addTable($factory, "my2");
		self::addTable($factory, "hft3");
		self::addTable($factory, "hft2");
		self::addTable($factory, "aurora");
		self::addTable($factory, "ontrack");
		self::addTable($factory, "my2");
		self::addTable($factory, "hft3");
		self::addTable($factory, "hft2");
		self::addTable($factory, "aurora");
		self::addTable($factory, "ontrack");
		self::addTable($factory, "my2");
		self::addTable($factory, "hft3");
		self::addTable($factory, "hft2");
		self::addTable($factory, "aurora");
	}
}
