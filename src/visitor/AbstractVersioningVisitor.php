<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

abstract class AbstractVersioningVisitor extends AbstractNodeElementVisitorInterface
{
    const MAX_COMMITS = 1;

    /**
     * @param $path string
     * @return array[int]commit
     */
    abstract protected function getCommits($path);

    public function visitNode(Node &$node)
    {
        // Full path is local to the project directory.
        // Explode it on '::' and get the first index to get the file path that is in git
        $commits = $this->getCommits(explode('::', $node->getFullPath())[0]);
        if (!count($commits)) {
            return;
        }

        $versioning = new Versioning($commits, self::MAX_COMMITS);
        $node->addElement($versioning);
    }
}
