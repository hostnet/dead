<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

interface NodeElementInterface
{
    public function accept(NodeElementVisitorInterface $visitor);
}
