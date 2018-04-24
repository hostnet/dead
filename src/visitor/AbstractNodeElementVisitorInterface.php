<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

abstract class AbstractNodeElementVisitorInterface implements NodeElementVisitorInterface
{
    public function visitDynamicAnalysis(DynamicAnalysis &$dynamic_analysis)
    {
    }

    public function visitFileChange(FileChange &$file_change)
    {
    }

    public function visitVersioning(Versioning &$versioning)
    {
    }

    public function visitNode(Node &$node)
    {
    }

    public function visitNodeFirst(Node &$node)
    {
    }

    public function visitAst(Ast &$ast)
    {
    }

    public function __toString()
    {
        return "<" . get_class($this) . ">";
    }
}
