<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

class FileChange implements NodeElementInterface
{

    /**
     * Date and time when the file was added to
     * the system under inspection.
     *
     * @var DateTime
     */
    private $added_at;

    /**
     * Date and time when the file was removed
     * from the system under inspection.
     *
     * @var DateTime
     */
    private $deleted_at;


    /**
     * The number of children the Node has
     * @var int
     */
    private $children;

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
        return $this->firstHit;
    }

    /**
     * @return DateTime
     * @see self::lastHit
     */
    public function getLastHit()
    {
        return $this->lastHit;
    }

    /**
     * @return DateTime
     * @see self::addedAt
     */
    public function getAddedAt()
    {
        return $this->added_at;
    }

    /**
     * @return DateTime
     * @see self::$deletedAt
     */
    public function getDeletedAt()
    {
        return $this->deleted_at;
    }


    /**
     * @param DateTime $added_at
     * @param DateTime $deleted_at
     */
    public function __construct($added_at, $deleted_at)
    {

        $this->added_at   = $added_at;
        $this->deleted_at = $deleted_at;
    }


    public function accept(NodeElementVisitorInterface $visitor)
    {
        $visitor->visitFileChange($this);
    }


    public function __toString()
    {
        return "<FileChange/>";
    }
}
