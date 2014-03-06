<?php

class GitFileTreeFactory extends AbstractTreeFactory
{

    private $files = array();

    private $git_command = 'git ls-tree -r --full-tree HEAD --name-only';

    /**
     *
     * @param $path string            
     * @param $extension string            
     * @return void
     */
    public function scan($path, $extension = 'php')
    {
        try {
            $path = realpath($path);
            $handle = popen('cd ' . escapeshellarg($path) . ' && ' . $this->git_command, 'r');
            $resource_iterator = new ResourceIterator($handle);
            $filter_iterator = new FileInfoFilterIterator($resource_iterator);
            $filter_iterator->setFindExtension($extension);
            
            foreach ($filter_iterator as $file) {
                    $this->files[] = new Node($path . DIRECTORY_SEPARATOR . $file, $file);
            }
            pclose($handle);
        } catch (UnexpectedValueException $e) {
            echo "Could not open dir $path" . PHP_EOL;
        } catch (Exception $e) {
            die($e->getMessage());
        }
        return $this;
    }

    public function &produceList()
    {
        return $this->files;
    }
}
