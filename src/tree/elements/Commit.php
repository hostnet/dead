<?php

/**
 * @copyright 2018 Hostnet B.V.
 */
declare(strict_types=1);
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
        $this->id      = $id;
        $this->author  = $author;
        $this->date    = $date;
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
