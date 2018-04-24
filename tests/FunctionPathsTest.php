<?php
/**
 * @copyright 2018 Hostnet B.V.
 */
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class FunctionPathsTest extends TestCase
{

    public function testRun()
    {
        $this->markTestIncomplete("Test is not yet ready because of edge case");
        $current_location = __DIR__;
        $results          = [];
        $expected_results = [
            $current_location."/fixtures/A.php::test1",
            $current_location."/fixtures/A.php::inTest1",
            $current_location."/fixtures/A.php::test2",
            $current_location."/fixtures/ClassB.php::ClassB::__construct",
            $current_location."/fixtures/ClassB.php::ClassB::test3",
            $current_location."/fixtures/ClassB.php::ClassB::inTest3",
            $current_location."/fixtures/ClassC.php::Dead\TestNamespace\ClassC::test4",
            $current_location."/fixtures/ClassC.php::Dead\TestNamespace::test5",
            $current_location."/fixtures/ClassC.php::Dead\TestNamespace\ClassD::test6",
        ];

        $files = (new FileTreeFactory())->scan("./fixtures")->produceList();
        foreach ($files as $file) {
            $file_functions = (new FunctionPathsFactory())->produceList($file);
            foreach ($file_functions as $file_function) {
                $results[] = $file_function->getFunction();
            }
        }
        $difference = array_diff($expected_results, $results);
        self::assertEmpty($difference, "Not all functions have been found or formatted correctly.");
    }
}
