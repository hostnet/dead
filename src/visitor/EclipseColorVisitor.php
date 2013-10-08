<?php

class EclipseColorVisitor extends AbstractNodeElementVisitor
{
    /**
     * @var Resource
     */
    private $stream;
    private $count;
    private $cutoff;

    private $dynamicAnalysis;

    public function __construct($filename, $workspacePath = "")
    {
        $this->stream = fopen($filename, 0x777);
        $this->cutoff = $workspacePath;
    }

    public function __destruct()
    {
        fclose($this->stream);
    }

    /**
     * @param DynamicAnalysis $dynamicAnalysis
     */
    public function visitDynamicAnalysis(DynamicAnalysis &$dynamicAnalysis)
    {
        $this->dynamicAnalysis = &$dynamicAnalysis;
    }

    public function visitNodeFirst(Node &$node)
    {
        $node->aggregateTree();
    }

    /**
     * (non-PHPdoc)
     * Now we know all elements of the node are handled
     * Time to transform the collected data
     * @see AbstraceNodeElementVisitor::visitNode()
     */
    public function visitNode(Node &$node)
    {
        $element = $this->dynamicAnalysis;
        $path = $this->getWorkspacePath($node->getFullPath());
        if ($path !== false) {
            fprintf($this->stream, "%d %s\n", $element->getPctDead(), $path);
        }

    }

    private function getWorkspacePath($path)
    {
        $cutoff = $this->cutoff;
        if ($cutoff[strlen($cutoff) - 1] === DIRECTORY_SEPARATOR) {
            $cutoff = substr($cutoff, 0, -1);
        }

        if (strlen($cutoff) > strlen($path)) {
            $result = false;
        } elseif (strlen($cutoff) == strlen($path)) {
            $result = "/";
        } else {
            $result = substr($path, strlen($cutoff) + 1);
        }

        return $result;

    }
}
