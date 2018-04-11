<?php
/**
 * @copyright 2018 Hostnet B.V.
 */
declare(strict_types=1);

class FunctionPathTest extends \PHPUnit\Framework\TestCase
{
    public function testRun()
    {
        $current_location = getcwd();
        $expected_results = [
            $current_location."/test_files/A.php::test1",
            $current_location."/test_files/A.php::inTest1",
            $current_location."/test_files/A.php::test2",
            $current_location."/test_files/ClassB.php::ClassB::__construct",
            $current_location."/test_files/ClassB.php::ClassB::test3",
            $current_location."/test_files/ClassB.php::ClassB::inTest3",
            $current_location."/test_files/ClassC.php::Dead\TestNamespace\ClassC::test4",
            $current_location."/test_files/ClassC.php::Dead\TestNamespace::test5",
            $current_location."/test_files/ClassC.php::Dead\TestNamespace\ClassD::test6",
        ];

        $files          = (new FileTreeFactory())->scan("./test_files")->produceList();
        $php_tokens     = new PhpTokensFactory($files);
        $function_paths = $php_tokens->produceList();
        $difference     = array_diff($expected_results, $function_paths);
        $this->assertEmpty($difference, "Not all functions have been found or formatted correctly.");
    }
}
