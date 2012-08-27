<?php
require_once("AbstractNodeElementVisitor.php");
require_once("tree/elements/Versioning.php");

abstract class AbstractVersioningVisitor extends AbstractNodeElementVisitor
{
    /*
     * @var $maxCommits int
     */
    private $maxCommits = 2;

    /**
     *
     * @param $path string       	
     * @return array[int]commit
     */
    abstract protected function getCommits($path, $max);

    public function visitNode(Node &$node)
    {
        $commits = $this
                ->getCommits($node->getLocation(), $this->maxCommits);
        if (count($commits)) {
            $versioning = new Versioning($commits, $this->maxCommits);
            $node->addElement($versioning);
        }

    }
}
