<?php
/**
 * @copyright 2018 Hostnet B.V.
 */
declare(strict_types=1);

class PrimeData
{
    /**
     * @var string
     */
    private $changed_at;

    /**
     * @var bool
     */
    private $dead;

    /**
     * @return string
     */
    public function getChangedAt()
    {
        return $this->changed_at;
    }

    public function getSQLChangedAt()
    {
        if ($this->changed_at !== "") {
            return "\"$this->changed_at\"";
        }

        return "NULL";
    }

    /**
     * @return bool
     */
    public function getDead()
    {
        return $this->dead;
    }

    /**
     * @param int $changed_at string
     * @param $dead boolean
     */
    public function __construct($changed_at = 0, $dead = false)
    {
        assert(is_string($changed_at));
        assert(is_bool($dead));

        $this->changed_at = $changed_at;
        $this->dead       = $dead;
    }

    public function __toString()
    {
        //$date = substr($this->changedAt,0,19);
        $date = $this->changed_at;

        return "<PrimeDate changedAt=\"$date\">";
    }
}
