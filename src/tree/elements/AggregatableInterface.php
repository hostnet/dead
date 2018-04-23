<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

interface AggregatableInterface
{
    public function aggregate($object);

    public function getAggregateKey();
}
