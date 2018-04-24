<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

class Ast implements NodeElementInterface
{
    /**
     * @var string
     */
    private $ast;

    public function accept(NodeElementVisitorInterface $visitor)
    {
        $visitor->visitAst($this);
    }

    /**
     * @param string $ast
     */
    public function __construct($ast)
    {
        assert(is_string($ast));
        $this->ast = $ast;
    }


    public function getAst()
    {
        return $this->ast;
    }
}
