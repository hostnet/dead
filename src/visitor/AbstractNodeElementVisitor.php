<?php


abstract class AbstractNodeElementVisitor implements INodeElementVisitor {
	public function visitDynamicAnalysis(DynamicAnalysis &$dynamicAnalysis) {
	}
	public function visitFileChange(FileChange &$fileChange) {
	}
	public function visitVersioning(Versioning &$versioning) {
	}
	public function visitNode(Node &$node) {
	}
	public function visitNodeFirst(Node &$node) {
	}
	public function visitAst(Ast &$ast)
	{
	}
	public function __toString() {
		return "<" . get_class ( $this ) . ">";
	}
}
