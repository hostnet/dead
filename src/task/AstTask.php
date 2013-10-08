<?php

class AstTask implements ITask
{
    private $path = "/home/ontw/aurora/www/web";
       
    public function run()
    {
      $astVisitor = new AstVisitor();
      $astFilesVisitor = new AstFilesVisitor();
      $nodes = $this->getFileNodes();

      for($i=0;$i<count($nodes);$i++) {
        $nodes[$i]->accept($astVisitor);
        $nodes[$i]->accept($astFilesVisitor);
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

?>