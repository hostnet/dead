<?php
require_once("AbstractNodeElementVisitor.php");
require_once("tree/elements/Versioning.php");

abstract class AbstractVersioningVisitor extends AbstractNodeElementVisitor
{

	  const max_commits = 1;

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
            $versioning = new Versioning($commits, self::max_commits);
            $node->addElement($versioning);
        }

    }
}
