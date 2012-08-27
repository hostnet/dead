<?php
interface INodeElement {
	public function accept(INodeElementVisitor $visitor);
}
