<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

abstract class AbstractVersioningVisitor extends AbstractNodeElementVisitorInterface
{

    const MAX_COMMITS = 1;

    /**
     *
     * @param $path string
     * @return array[int]commit
     */
    abstract protected function getCommits($path);

    public function visitNode(Node &$node)
    {
        $commits = $this
            ->getCommits($node->getLocation());
        if (count($commits)) {
            $versioning = new Versioning($commits, self::MAX_COMMITS);
            $node->addElement($versioning);
        }
    }
}
