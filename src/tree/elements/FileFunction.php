<?php
/**
 * @copyright 2018 Hostnet B.V.
 */

declare(strict_types=1);

class FileFunction implements INodeElement
{
    private $function;

    public function __construct(string $function)
    {
        $this->function = $function;
    }

    public function getFunction(): string
    {
        return $this->function;
    }

    public function accept(INodeElementVisitor $visitor)
    {
        $visitor->visitFunctionName($this);
    }
}