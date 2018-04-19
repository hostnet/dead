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
        } else {
            return $this->next();
        }
    }

    public function next()
    {
        return $this->current_line = rtrim(fgets($this->handle));
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
        if ($meta['seekable']) {
            rewind($handle);
        }
    }
}
