<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

class DynamicAnalysis implements NodeElementInterface, AggregatableInterface
{
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
    private $first_hit;

    /**
     * Date and time of the most recent inclusion
     * of the file in the system under inspection.
     *
     * @var DateTime
     */
    private $last_hit;


    /**
     * The number of files contained
     * by this node (including the node
     * itself)
     * @var int
     */
    private $file_count;


    /**
     * The number of dead files
     * @var int
     */
    private $dead_count;

    /**
     * @see self::$count
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @return DateTime
     */
    public function getFirstHit()
    {
        return $this->first_hit;
    }

    /**
     * @return DateTime
     * @see self::lastHit
     */
    public function getLastHit()
    {
        return $this->last_hit;
    }

    /**
     * @return int
     */
    public function getFileCount()
    {
        return $this->file_count;
    }

    /**
     * @return int
     */
    public function getDeadCount()
    {
        return $this->dead_count;
    }

    /**
     * @return double
     */
    public function getRatioDead()
    {
        return $this->dead_count / $this->file_count;
    }

    /**
     * @return int
     */
    public function getPctDead()
    {
        return (int) ($this->getRatioDead() * 100);
    }

    /**
     * @param int $count
     * @param DateTime $first_hit
     * @param DateTime $last_hit
     * @param int $file_count
     * @param null $dead_count
     */
    public function __construct($count, $first_hit, $last_hit, $file_count = 1, $dead_count = null)
    {
        assert(is_numeric($file_count) && $file_count >= 0);
        assert(is_numeric($count) && $count >= 0);
        assert($dead_count === null || is_numeric($dead_count) && $dead_count >= 0);
        assert($first_hit === null || $first_hit instanceof DateTime);
        assert($last_hit === null || $last_hit instanceof DateTime);

        $this->count      = $count;
        $this->first_hit  = $first_hit;
        $this->last_hit   = $last_hit;
        $this->file_count = $file_count;

        if ($dead_count === null) {
            $this->dead_count = $count > 0 ? 0 : 1;
        } else {
            $this->dead_count = $dead_count;
        }
    }


    public function accept(NodeElementVisitorInterface $visitor)
    {
        $visitor->visitDynamicAnalysis($this);
    }


    public function __toString()
    {
        return "<DynamicAnalysis fileCount=\"$this->file_count\" hits=\"$this->count\"\"/>";
    }


    /**
     * @param DynamicAnalysis $analysis
     * @return DynamicAnalysis
     */
    public function aggregate($analysis)
    {
        assert($analysis instanceof DynamicAnalysis);
        $count = bcadd($analysis->getCount(), $this->count);
        if ($count < 0) {
            die($count);
        }
        $first_hit  = max($analysis->getFirstHit(), $this->first_hit);
        $last_hit   = max($analysis->getLastHit(), $this->last_hit);
        $file_count = $analysis->getFileCount() + $this->file_count;
        $dead_count = $analysis->getDeadCount() + $this->dead_count;

        return new DynamicAnalysis($count, $first_hit, $last_hit, $file_count, $dead_count);
    }

    public function getAggregateKey()
    {
        return __CLASS__;
    }
}
