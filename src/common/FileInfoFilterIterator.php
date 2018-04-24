<?php
/**
 * @copyright 2014-2018 Hostnet B.V.
 */
declare(strict_types=1);

class FileInfoFilterIterator extends FilterIterator
{
    private $find_extensions;

    public function setFindExtension($extension)
    {
        $this->find_extensions = func_get_args();
    }

    public function accept()
    {
        $info = new SplFileInfo($this->current()->getPathname());
        if (in_array($info->getExtension(), $this->find_extensions)) {
            return true;
        }

        return false;
    }
}
