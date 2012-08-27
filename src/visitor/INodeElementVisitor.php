<?php

interface INodeElementVisitor {
	public function visitDynamicAnalysis(DynamicAnalysis &$dynamicAnalysis);
	public function visitFileChange(FileChange &$fileChange);
	public function visitVersioning(Versioning &$versioning);
	public function visitAst(Ast &$ast);
	public function visitNodeFirst(Node &$node);
}
 
?>
