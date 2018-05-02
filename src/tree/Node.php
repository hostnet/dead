<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

class Node implements NodeElementInterface
{
    private $children = [];
    private $elements = [];
    private $path;
    private $full_path;
    private $location;
    private $parent = null;

    public function __construct($full_path, $name = null)
    {
        // This won't have any effect on file paths, just on function paths
        $parts           = explode('::', $full_path);
        $parts[0]        = realpath($parts[0]) ?: $parts[0];
        $this->full_path = $full_path;
        $this->location  = join('::', $parts);

        if (strlen($full_path) <= 1) {
            $this->path = $full_path;
        } elseif ($name == null) {
            $this->path = substr(
                $full_path,
                strrpos($full_path, DIRECTORY_SEPARATOR) + 1
            );
        } else {
            $this->path = $name;
        }
    }

    /**
     * Returns true is this is a leave node
     * @return bool
     */
    public function isLeaf()
    {
        return count($this->children) == 0;
    }

    public function addChild(Node $node)
    {
        $node->setParent($this);
        $this->children[] = $node;
    }

    public function addElement(NodeElementInterface $element)
    {
        $this->elements[] = $element;
    }

    /**
     * @return the $path
     */
    public function getPath()
    {
        return $this->path;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function getFullPath()
    {
        return $this->full_path;
    }

    protected function &getChildren()
    {
        return $this->children;
    }

    /**
     * @param string $path
     * @return Node
     */
    public function &addChildByRelativePath($path)
    {
        /** @var Node $node */
        $node = null;

        if (array_key_exists($path, $this->children)) {
            $node = $this->children[$path];
        } else {
            $own_path              = $this->full_path != DIRECTORY_SEPARATOR ? $this->full_path
                : "";
            $node                  = new Node($own_path . DIRECTORY_SEPARATOR . $path, $path);
            $this->children[$path] = $node;
            $node->setParent($this);
        }

        return $node;
    }

    /**
     * Recursion for tree pushed down to visitor so
     * it can deside the traversing algorithm
     * @see NodeElementInterface::accept()
     */
    public function accept(NodeElementVisitorInterface $visitor)
    {
        $visitor->visitNodeFirst($this);
        foreach ($this->elements as $element) {
            $element->accept($visitor);
        }
        $visitor->visitNode($this);
    }

    public function __toString()
    {
        return $this->toStringSinge();
    }

    public function toStringSinge()
    {
        $elements = implode(",", $this->elements);

        return "<Node path=\"$this->path\" elements=$elements>";
    }

    public function toStringRecursive($indent = "  ")
    {
        $string = $this->toStringSinge();
        foreach ($this->children as $child) {
            /** @var Node $child */
            $string .= PHP_EOL . $indent
                . $child->toStringRecursive($indent . "  ");
        }

        return $string;
    }

    public function acceptBroadFirst($visitor)
    {
        $this->accept($visitor);
        foreach ($this->getChildren() as $child) {
            $child->acceptBroadFirst($visitor);
        }
    }

    public function acceptDepthFirst($visitor)
    {
        foreach ($this->getChildren() as $child) {
            $child->acceptDepthFirst($visitor);
        }
        $this->accept($visitor);
    }

    /**
     * Returns true if this is a node without a parent
     * A node without parent is a root node.
     * @return bool
     */
    public function isRoot()
    {
        return $this->parent == null;
    }

    /**
     * @return Node
     * @throws Exception
     */
    public function &getParent()
    {
        if ($this->parent == null) {
            throw new Exception("root node has no parent");
        }

        return $this->parent;
    }

    /**
     * @param Node $parent
     */
    public function setParent(Node &$parent)
    {
        $this->parent = &$parent;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function aggregateTree()
    {
        $this->aggregateElements();
        foreach ($this->elements as $element) {
            if (!($this->parent instanceof Node)) {
                continue;
            }

            if (!($element instanceof AggregatableInterface)) {
                continue;
            }

            $this->getParent()->addElement($element);
        }
    }

    /**
     * @return void
     */
    public function aggregateElements()
    {
        $sorted_elements = [];

        foreach ($this->elements as $key => $element) {
            if (!($element instanceof AggregatableInterface)) {
                continue;
            }

            $index = $element->getAggregateKey();

            if (isset($sorted_elements[$index])) {
                $sorted_elements[$index] = $sorted_elements[$index]
                    ->aggregate($element);
            } else {
                $sorted_elements[$index] = $element;
            }

            unset($this->elements[$key]);
        }

        foreach ($sorted_elements as $element) {
            $this->elements[] = $element;
        }
    }
}
