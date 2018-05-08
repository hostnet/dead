<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

abstract class AbstractTreeFactoryInterface implements TreeFactoryInterface
{
    /**
     * @return Tree
     */
    public function &produceTree()
    {
        $leaves = $this->produceList();
        $root   = new Tree("/"); // Create the root node of the file tree

        foreach ($leaves as $key => $node) {
            /* @var $node Node */
            $pointer       = &$root; // Create a reference for tree walking
            $path          = explode(DIRECTORY_SEPARATOR, trim($node->getLocation(), DIRECTORY_SEPARATOR));
            $function_path = explode('::', $path[count($path) - 1]);
            $path[count($path) - 1] = $function_path[0];

            foreach ($path as $part) {
                $pointer = &$pointer->addChildByRelativePath($part);
            }

            $pointer->addChild($leaves[$key]);
        }

        return $root;
    }
}
