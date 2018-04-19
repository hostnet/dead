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
	private $functions = [];

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

	public function visitFunctionName(FileFunction $file_function)
	{
		$this->functions[] = $file_function->getFunction();
	}

	public function visitNode(Node &$node) {
		$changedAt = "";
		$dead = false;
		$file_functions = [];

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

		foreach ($this->functions as $function) {
			$file_functions[] = $function;
		}

		$prime_data = new PrimeData ( $changedAt, $dead, $file_functions);

		if($this->prefix) {
			$this->data [$this->prefix . $node->getPath ()] = $prime_data;
		} else {
			$this->data [$node->getFullPath ()] = $prime_data;
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
     * @var array
     */
    private $file_functions;

    /**
     * @param array $file_functions
     */
    public function setFileFunctions(array $file_functions): void
    {
        $this->file_functions = $file_functions;
    }

    /**
     * @return array
     */
    public function getFileFunctions(): array
    {
        return $this->file_functions;
    }

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
	 * @param int $changedAt string
	 * @param $dead boolean
	 * @param array $file_functions
	 */
	public function __construct($changedAt = 0, $dead = false, $file_functions = [])
	{
		assert ( is_string ( $changedAt ) );
		assert ( is_bool ( $dead ) );
		assert(is_array($file_functions));

		$this->changedAt = $changedAt;
		$this->dead = $dead;
		$this->file_functions = $file_functions;

	}
	
	public function __toString() {
		//$date = substr($this->changedAt,0,19);
		$date = $this->changedAt;
		return "<PrimeDate changedAt=\"$date\">";
	}

}
?>