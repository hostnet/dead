<?php
require_once("AbstractNodeElementVisitor.php");

class PDOCacheTreeVisitor extends AbstractNodeElementVisitor
{

    private $dynamicAnalysis;
    private $versioning;
    private $data = array();

    public function __construct()
    {

    }
    public function visitNode(Node &$node)
    {
        // Put all the data in the structure
        $this->data[] = array("file" => $node->getFullPath(),
                "count" => (int) $this->dynamicAnalysis->getCount(),
                "file_count" => (int) $this->dynamicAnalysis->getFileCount(),
                "dead_count" => (int) $this->dynamicAnalysis->getDeadCount(),
                "first_hit" => $this->dynamicAnalysis->getFirstHit(),
                "last_hit" => $this->dynamicAnalysis->getLastHit(),
                "changed_at" => $this->versioning->getLastChange());
    }

    public function visitNodeFirst(Node &$node)
    {
        $node->aggregateTree();
    }

    public function visitDynamicAnalysis(DynamicAnalysis &$dynamicAnalysis)
    {
        $this->dynamicAnalysis = $dynamicAnalysis;
    }

    public function visitVersioning(Versioning &$versioning)
    {
        $this->versioning = $versioning;
    }

    public function getData()
    {
        return $this->data;
    }
}
