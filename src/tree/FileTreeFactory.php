<?php
/**
 * @copyright 2018 Hostnet B.V.
 */
declare(strict_types=1);

class FileTreeFactory extends AbstractTreeFactoryInterface
{
    private $functions = [];

    /**
     * @param $path string
     * @param $extension string
     * @return self
     */
    public function scan($path, $extension = 'php')
    {
        try {
            $directory_iterator = new RecursiveDirectoryIterator($path);
            $recursive_iterator = new RecursiveIteratorIterator($directory_iterator);
            $filter_iterator    = new FileInfoFilterIterator($recursive_iterator);
            $filter_iterator->setFindExtension($extension);
            $path_length = strlen($path);
            foreach ($filter_iterator as $file) {
                $this->addFunctionsFromFile($file->getPathname());
            }
        } catch (UnexpectedValueException $e) {
            echo "Could not open dir $path" . PHP_EOL;
        } catch (\Throwable $e) {
            die($e->getMessage());
        }

        return $this;
    }

    /**
     * When a file is added scan it for functions and add them to the file.
     * @param $filename string
     * @return FileTreeFactory
     */
    public function addFunctionsFromFile($filename): self
    {
        $node                   = new Node($filename);
        $function_paths_factory = new FunctionPathsFactory();

        foreach ($function_paths_factory->produceList($node) as $function) {
            $this->functions[] = $function;
        }

        return $this;
    }

    public function &produceList()
    {
        return $this->functions;
    }
}
