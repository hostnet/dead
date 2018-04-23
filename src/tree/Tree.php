<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

class Tree extends Node
{
    public function __toString()
    {
        return $this->toStringRecursive();
    }
}
