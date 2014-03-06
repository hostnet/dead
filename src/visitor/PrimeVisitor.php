<?php

class PrimeVisitor extends AbstractNodeElementVisitor {
	
	/**
	 *
	 * @var FileChange
	 */
	private $filechange = null;
	
	/**
	 *
	 * @var Versioning
	 */
	private $versioning = null;
	
	/**
	 *
	 * @var array[string]PrimeData
	 */
	private $data = array ();
	
	/**
	 * 
	 * @var string
	 */
	private $prefix = null;
	
	public function visitVersioning(Versioning &$versioning) {
		$this->versioning = $versioning;
	}
	
	public function visitFileChange(FileChange &$fileChange) {
		$this->fileChange = $fileChange;
	}
	
	public function visitNode(Node &$node) {
		$changedAt = "";
		$dead = false;
		
		if ($this->versioning !== null) {
			$lastChange = $this->versioning->getLastChange();
			if($lastChange !== null) {
			
			$timezone = new DateTimeZone(date_default_timezone_get());
			
			$lastChange->setTimezone($timezone);
			$changedAt  = $lastChange->format("Y-m-d H:i:s");
			}
			$this->versioning = null;
		}
		
		if ($this->filechange !== null) {
			$dead = is_null ( $this->filechange->getDeletedAt () );
		}
		
		if($this->prefix) {
		    $this->data [$this->prefix . $node->getPath ()] = new PrimeData ( $changedAt, $dead );
		} else {
		  $this->data [$node->getFullPath ()] = new PrimeData ( $changedAt, $dead );
		}
	}
	
	/**
	 *
	 * @return array[int]PrimeData
	 */
	public function getPrimeData() {
		return $this->data;
	}
	
	public function reset() {
		$this->data = array ();
	}
	
	public function setPrefix($prefix) {
	    $this->prefix = $prefix;
	}

}

class PrimeData {
	
	/**
	 *
	 * @var string
	 */
	private $changedAt;
	
	/**
	 *
	 * @var boolean
	 */
	private $dead;
	
	/**
	 *
	 * @return string
	 */
	public function getChangedAt() {
		return $this->changedAt;
	}
	
	public function getSQLChangedAt() {
		if ($this->changedAt !== "") {
			return "\"$this->changedAt\"";
		}	else {
			return "NULL";
		}
	}
	
	/**
	 *
	 * @return boolean
	 */
	public function getDead() {
		return $this->dead;
	}
	
	/**
	 *
	 * @param $fullPath string       	
	 * @param $changedAt string       	
	 * @param $dead boolean       	
	 */
	public function __construct($changedAt = 0, $dead = false) {
		assert ( is_string ( $changedAt ) );
		assert ( is_bool ( $dead ) );
		
		$this->changedAt = $changedAt;
		$this->dead = $dead;
	}
	
	public function __toString() {
		//$date = substr($this->changedAt,0,19);
		$date = $this->changedAt;
		return "<PrimeDate changedAt=\"$date\">";
	}

}
?>