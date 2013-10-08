<?php

class FileChange implements INodeElement {
	
	/**
	 * Date and time when the file was added to
	 * the system under inspection.
	 * 
	 * @var DateTime
	 */
	private $addedAt;
	
	/**
	 * Date and time when the file was removed
	 * from the system under inspection.
	 * 
	 * @var DateTime
	 */
	private $deletedAt;
	
	
	/**
	 * The number of children the Node has
	 * @var int
	 */
	private $children;
	
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
	 * @return DateTime
	 * @see self::addedAt
	 */
	public function getAddedAt() {
		return $this->addedAt;
	}

	/**
	 * @return DateTime
	 * @see self::$deletedAt
	 */
	public function getDeletedAt() {
		return $this->deletedAt;
	}


	/**
	 * @param DateTime $addedAt
	 * @param DateTime $deletedAt
	 */
	public function __construct($addedAt, $deletedAt) {

		$this->addedAt = $addedAt;
		$this->deletedAt = $deletedAt;

	}
	
	
	public function accept(INodeElementVisitor $visitor) {
		$visitor->visitFileChange( $this );
	}
	
	
	
	
	
	public function __toString() {
		return "<FileChange/>";
	}
}
