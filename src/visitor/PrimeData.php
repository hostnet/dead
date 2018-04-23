<?php
/**
 * @copyright 2018 Hostnet B.V.
 */
declare(strict_types=1);

class PrimeData
{

    /**
     *
     * @var string
     */
    private $changed_at;

    /**
     *
     * @var boolean
     */
    private $dead;

    /**
     * @var FileFunction[]
     */
    private $file_functions;

    /**
     * @param FileFunction[]
     */
    public function setFileFunctions(array $file_functions): void
    {
        $this->file_functions = $file_functions;
    }

    /**
     * @return FileFunction[]
     */
    public function getFileFunctions(): array
    {
        return $this->file_functions;
    }

    /**
     *
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
        } else {
            return "NULL";
        }
    }

    /**
     *
     * @return boolean
     */
    public function getDead()
    {
        return $this->dead;
    }

    /**
     *
     * @param int $changed_at string
     * @param $dead boolean
     * @param FileFunction[] $file_functions
     */
    public function __construct($changed_at = 0, $dead = false, $file_functions = [])
    {
        assert(is_string($changed_at));
        assert(is_bool($dead));
        assert(is_array($file_functions));

        $this->changed_at     = $changed_at;
        $this->dead           = $dead;
        $this->file_functions = $file_functions;
    }

    public function __toString()
    {
        //$date = substr($this->changedAt,0,19);
        $date = $this->changed_at;

        return "<PrimeDate changedAt=\"$date\">";
    }
}
