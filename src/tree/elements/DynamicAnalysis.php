<?php
require_once "INodeElement.php";
require_once "IAggregatable.php";

class DynamicAnalysis implements INodeElement, IAggregatable {
	
	/**
	 * The numer of times a file is included by the
	 * application under inspection.
	 * 
	 * @var long
	 */
	private $count;
	
	/**
	 * Date and time on which this file is included
	 * for the first time by the system meassured.
	 * 
	 * @var DateTime
	 */
	private $firstHit;
	
	/**
	 * Date and time of the most recent inclusion
	 * of the file in the system under inspection.
	 *
	 * @var DateTime
	 */
	private $lastHit;
	
		
	/**
	 * The number of files contained
	 * by this node (including the node
	 * itself)
	 * @var int
	 */
	private $fileCount;
	
	
	/**
	 * The number of dead files
	 * @var int
	 */
	private $deadCount;
	/**
	 * @see self::$count
	 * @return int
	 */
	public function getCount() {
		return $this->count;
	}

	/**
	 * @return DateTime
	 */
	public function getFirstHit() {
		return $this->firstHit;
	}

	/**
	 * @return DateTime
	 * @see self::lastHit
	 */
	public function getLastHit() {
		return $this->lastHit;
	}

	/**
	 * @return int
	 */
	public function getFileCount() {
		return $this->fileCount;
	}
	
	/**
	 * @return int
	 */
	public function getDeadCount() {
		return $this->deadCount;
	}
	
	/**
	 * @return double
	 */
	public function getRatioDead() {
		return $this->deadCount / $this->fileCount;
	}
	
	/**
	 * @return int
	 */
	public function getPctDead() {
		return (int) ($this->getRatioDead() * 100);
	}

	/**
	 * 
	 * @param int $count
	 * @param DateTime $firstHit
	 * @param DateTime $lastHit
	 * @param int $children
	 */
	public function __construct($count, $firstHit, $lastHit, $fileCount = 1, $deadCount = null) {
		assert(is_numeric($fileCount) && $fileCount >= 0 );
		assert(is_numeric($count) && $count >= 0 );
		assert($deadCount === null || is_numeric($deadCount) && $deadCount >= 0 );
		assert($firstHit === null || $firstHit instanceOf DateTime);
		assert($lastHit === null || $lastHit instanceOf DateTime);
		
		$this->count = $count;
		$this->firstHit = $firstHit;
		$this->lastHit = $lastHit;
		$this->fileCount = $fileCount;
		
		if($deadCount === null) {
			$this->deadCount = $count > 0 ? 0 : 1; 
		} else {
			$this->deadCount = $deadCount;
		}
	}
	
	
	public function accept(INodeElementVisitor $visitor) {
		$visitor->visitDynamicAnalysis ( $this );
	}
	

	public function __toString() {
		return "<DynamicAnalysis fileCount=\"$this->fileCount\" hits=\"$this->count\"\"/>";
	}
	
		
	/**
	 * 
	 * @param DynamicAnalysis $analysis
	 * @return DynamicAnalysis
	 */
	public function aggregate( $analysis) {
		assert($analysis instanceof DynamicAnalysis);
		$count = bcadd($analysis->getCount(), $this->count);
		if($count < 0) die($count);
		$firstHit = max($analysis->getFirstHit(), $this->firstHit);
		$lastHit = max($analysis->getLastHit(), $this->lastHit);
		$fileCount = $analysis->getFileCount() + $this->fileCount;
		$deadCount = $analysis->getDeadCount() + $this->deadCount;
		return new DynamicAnalysis($count, $firstHit, $lastHit,$fileCount,$deadCount);
	}
	
	public function getAggregateKey() {
		return __CLASS__;
	}
}
