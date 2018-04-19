<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

class AstVisitor extends AbstractNodeElementVisitorInterface
{
    private $phc_command    = "/usr/bin/env phc";
    private $phc_ast_option = "--dump-ast-xml";

    public function __construct()
    {
        $this->checkVersion();
    }

    private function checkVersion()
    {
        if (Version::compare($this->execVersion(), '0.1.7')
            == Version::SMALLER) {
            throw new Exception("Need phc installed (version 0.1.7 or higher");
        }
    }

    private function execVersion()
    {
        $version = `$this->phcCommand    --version`;
        $return  = "";

        if (substr($version, 0, 3) == "phc") {
            $return = substr($version, 4);
        }

        return $return;
    }

    private function execPhc($filename)
    {
        return `$this->phc_command  $this->phc_ast_option  $filename  2>&1`;
    }

    public function visitNode(Node &$node)
    {
        $filename = $node->getLocation();
        $ast      = new Ast($this->execPhc($filename));
        $node->addElement($ast);
    }

    public function visitFunctionName(FileFunction $file_function)
    {
        // TODO: Implement visitFunctionName() method.
    }
}
