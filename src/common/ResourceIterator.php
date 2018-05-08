<?php
/**
 * @copyright 2014-2018 Hostnet B.V.
 */
declare(strict_types=1);

class ResourceIterator implements Iterator
{
    private $current_line = null;

    private $handle;

    public function __construct($handle)
    {
        assert(is_resource($handle));
        $this->handle = $handle;
    }

    public function current()
    {
        if ($this->current_line) {
            return $this->current_line;
        }

        return $this->next();
    }

    public function next()
    {
        $fgets = fgets($this->handle);
        if ($fgets) {
            return $this->current_line = rtrim($fgets);
        }
    }

    public function key()
    {
        return null;
    }

    public function valid()
    {
        return !feof($this->handle);
    }

    public function rewind()
    {
        $meta = stream_get_meta_data($this->handle);
        if (!$meta['seekable']) {
            return;
        }

        rewind($handle);
    }
}
