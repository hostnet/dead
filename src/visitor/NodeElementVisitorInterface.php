<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

interface NodeElementVisitorInterface
{
    public function visitDynamicAnalysis(DynamicAnalysis &$dynamic_analysis);

    public function visitFileChange(FileChange &$file_change);

    public function visitVersioning(Versioning &$versioning);

    public function visitAst(Ast &$ast);

    public function visitNodeFirst(Node &$node);

    public function visitFunctionName(FileFunction $file_function);
}
