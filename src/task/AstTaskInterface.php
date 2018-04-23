<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

class AstTaskInterface implements TaskInterface
{
    private $path = "/home/ontw/aurora/www/web";

    public function run()
    {
        $ast_visitor       = new AstVisitor();
        $ast_files_visitor = new AstFilesVisitor();
        $nodes             = $this->getFileNodes();

        for ($i = 0; $i < count($nodes); $i++) {
            $nodes[$i]->accept($ast_visitor);
            $nodes[$i]->accept($ast_files_visitor);
        }
    }

    /**
     * @return array[int]Nodes
     */
    private function getFileNodes()
    {
        // Read all file names from disk
        $factory = new FileTreeFactory();
        $factory->scan($this->path);

        return $factory->produceList();
    }
}
