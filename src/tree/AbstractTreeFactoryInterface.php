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

        foreach ($leaves as $key => $file_function) {
            /* @var $file_function FileFunction */
            $pointer = &$root; // Create a reference for tree walking
            $node    = new Node($leaves[$key]->getFunction());
            $path    = explode(DIRECTORY_SEPARATOR, trim($file_function->getFunction(), DIRECTORY_SEPARATOR));
            array_pop($path);

            foreach ($path as $part) {
                $pointer = &$pointer->addChildByRelativePath($part);
            }

            $pointer->addChild($node);
        }

        return $root;
    }
}
