<?php

class Commit
{
    private $id;
    private $author;
    private $message;
    private $date;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    public function __construct($id, $author, $date, $message)
    {
        $this->id = $id;
        $this->author = $author;
        $this->date = $date;
        $this->message = $message;
    }

    /**
     * used for unique, so keep id in representation
     */
    public function __toString()
    {
        if ($this->date !== null) {
            $date = $this->date->format("Y-m-d H:m:s");
        } else {
            $date = "";
        }
        return "<Commit date=\"$date\" id=\"$this->id\"/>";
    }
}

class Versioning implements INodeElement, IAggregatable
{

  /**
   * 
   * @var array[int]commit
   */  
  private $commits = array();
    
    /**
     * 
     * @var int
     */ 
    private $maxCommits;

    public function __construct(array $commits, $maxCommits)
    {
        assert(is_int($maxCommits));
        $this->commits = $commits;
        $this->maxCommits = $maxCommits;
    }

    public function accept(INodeElementVisitor $visitor)
    {
        $visitor->visitVersioning($this);
    }

    public function __toString()
    {
        $commits = implode(",", $this->commits);
        return "<Versioning commits=$commits>";
    }

    /**
     * @return DateTime
     */
    public function getLastChange()
    {
        $last = null;

        if (isset($this->commits[0])) {
            $last = $this->commits[0]->getDate();
        }

        return $last;
    }
    /**
     * @see IAggregatable::aggregate()
     */
    public function aggregate($versioning)
    {
        assert($versioning instanceof Versioning);
        $commits = array_merge($this->commits, $versioning->commits);
        $commits = array_unique($commits);
        rsort($commits);
        $commits = array_slice($commits, 0, $this->maxCommits);
                
		return new Versioning($commits,$this->maxCommits);

    }

    /**
     * @see IAggregatable::getAggregateKey()
     */
    public function getAggregateKey()
    {
        return __CLASS__;
    }

}

?>