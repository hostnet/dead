<?php

class AstVisitor extends AbstractNodeElementVisitor
{
    private $phcCommand = "/usr/bin/env phc";
    private $phcAstOption = "--dump-ast-xml";

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
        $return = "";

        if (substr($version, 0, 3) == "phc") {
            $return = substr($version, 4);
        }

        return $return;
    }

    private function execPhc($filename)
    {
        return `$this->phcCommand  $this->phcAstOption  $filename  2>&1`;

    }

    public function visitNode(Node &$node)
    {
        $filename = $node->getLocation();
        $ast = new Ast($this->execPhc($filename));
        $node->addElement($ast);
    }
}
