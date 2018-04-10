<?php

class FunctionPathTest extends \PHPUnit\Framework\TestCase
{
    public function testRun()
    {
        $files = (new FileTreeFactory())->scan("./test_files")->produceList();
        $PHPTokens = new PHPTokensFactory($files);
        $functionsPaths = $PHPTokens->produceList();
        var_dump($functionsPaths);
    }
}
