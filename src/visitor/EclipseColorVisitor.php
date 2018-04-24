<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

class EclipseColorVisitor extends AbstractNodeElementVisitorInterface
{
    /**
     * @var Resource
     */
    private $stream;
    private $count;
    private $cutoff;

    private $dynamic_analysis;

    public function __construct($filename, $workspace_path = "")
    {
        $this->stream = fopen($filename, 0x777);
        $this->cutoff = $workspace_path;
    }

    public function __destruct()
    {
        fclose($this->stream);
    }

    /**
     * @param DynamicAnalysis $dynamic_analysis
     */
    public function visitDynamicAnalysis(DynamicAnalysis &$dynamic_analysis)
    {
        $this->dynamic_analysis = &$dynamic_analysis;
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
     * @param Node $node
     */
    public function visitNode(Node &$node)
    {
        $element = $this->dynamic_analysis;
        $path    = $this->getWorkspacePath($node->getFullPath());
        if ($path === false) {
            return;
        }

        fprintf($this->stream, "%d %s\n", $element->getPctDead(), $path);
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

    public function visitFunctionName(FileFunction $file_function)
    {
        // TODO: Implement visitFunctionName() method.
    }
}
