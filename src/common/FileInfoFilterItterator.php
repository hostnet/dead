<?php

class FileInfoFilterIterator extends FilterIterator
{

    private $find_extensions;

    public function setFindExtension($extension)
    {
        $this->find_extensions = func_get_args();
    }

    public function accept()
    {
        $info = new SplFileInfo($this->current());
        if (in_array($info->getExtension(), $this->find_extensions)) {
            return true;
        } else {
            return false;
        }
    }
}