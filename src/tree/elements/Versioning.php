<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

class Versioning implements NodeElementInterface, AggregatableInterface
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
    private $max_commits;

    public function __construct(array $commits, $max_commits)
    {
        assert(is_int($max_commits));
        $this->commits     = $commits;
        $this->max_commits = $max_commits;
    }

    public function accept(NodeElementVisitorInterface $visitor)
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
     * @see AggregatableInterface::aggregate()
     */
    public function aggregate($versioning)
    {
        assert($versioning instanceof Versioning);
        $commits = array_merge($this->commits, $versioning->commits);
        $commits = array_unique($commits);
        rsort($commits);
        $commits = array_slice($commits, 0, $this->max_commits);

        return new Versioning($commits, $this->max_commits);
    }

    /**
     * @see AggregatableInterface::getAggregateKey()
     */
    public function getAggregateKey()
    {
        return __CLASS__;
    }
}
