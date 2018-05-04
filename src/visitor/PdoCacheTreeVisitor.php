<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

class PdoCacheTreeVisitor extends AbstractNodeElementVisitorInterface
{
    private $dynamic_analysis;
    private $versioning;
    private $data = [];

    public function __construct()
    {
    }

    public function visitNode(Node &$node)
    {
        // Put all the data in the structure
        $this->data[] = [
            "function"   => $node->getFullPath(),
            'changed_at' => $this->versioning->getLastChange(),
        ];
    }

    public function visitNodeFirst(Node &$node)
    {
        $node->aggregateTree();
    }

    public function visitDynamicAnalysis(DynamicAnalysis &$dynamic_analysis)
    {
        $this->dynamic_analysis = $dynamic_analysis;
    }

    public function visitVersioning(Versioning &$versioning)
    {
        $this->versioning = $versioning;
    }

    public function getData()
    {
        return $this->data;
    }

    public function visitFunctionName(FileFunction $file_function)
    {
        // TODO: Implement visitFunctionName() method.
    }
}
