<?php
require_once "INodeElement.php";

class Ast implements INodeElement
{
  /**
   * 
   * @var string
   */
  private $ast;
  
  public function accept(INodeElementVisitor $visitor)
  {
    $visitor->visitAst($this);   
  }

  /**
   * 
   * @param string $ast
   */
  public function __construct($ast) {
    assert(is_string($ast));
    $this->ast = $ast;
  }
  
  
  public function getAst() {
    return $this->ast;
  }

}
