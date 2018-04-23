<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

interface TreeFactoryInterface
{

    /**
     * @return array[int]Node
     */
    public function &produceList();

    /**
     * @return Tree
     */
    public function &produceTree();
}
