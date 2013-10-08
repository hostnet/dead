<?php

class Node implements INodeElement
{
    private $children = array();
    private $elements = array();
    private $path;
    private $fullPath;
    private $location;
    private $parent = null;

    public function __construct($fullPath, $name = NULL)
    {
        $this->fullPath = $fullPath;
        $this->location = realpath($fullPath) ? : $fullPath;

        if (strlen($fullPath) <= 1) {
            $this->path = $fullPath;
        } elseif ($name == NULL) {
            $this->path = substr($fullPath,
                    strrpos($fullPath, DIRECTORY_SEPARATOR) + 1);
        } else {
            $this->path = $name;
        }
    }

    /**
     * Returns true is this is a leave node
     * @return boolean
     */
    public function isLeaf()
    {
        return count($this->children) == 0;
    }

    public function addChild(Node &$node)
    {
        $node->setParent($this);
        $this->children[] = &$node;
    }

    public function addElement(INodeElement $element)
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
        return $this->fullPath;
    }

    protected function &getChildren()
    {
        return $this->children;
    }

    /**
     * 
     * @param string $path
     * @return Node
     */
    public function &addChildByRelativePath($path)
    {
        /* @var $node Node */
        $node = null;

        if (array_key_exists($path, $this->children)) {
            $node = $this->children[$path];
        } else {
            $ownPath = $this->fullPath != DIRECTORY_SEPARATOR ? $this->fullPath
                    : "";
            $node = new Node($ownPath . DIRECTORY_SEPARATOR . $path, $path);
            $this->children[$path] = $node;
            $node->setParent($this);
        }

        return $node;
    }

    /**
     * Recursion for tree pushed down to visitor so
     * it can deside the traversing algorithm
     * @see INodeElement::accept()
     */
    public function accept(INodeElementVisitor $visitor)
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
            /* @var $child Node */
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
     * @return boolean
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
     * 
     * @param Node $parent
     */
    public function setParent(Node &$parent)
    {
        $this->parent = &$parent;
    }

    /**
     * @return void
     */
    public function aggregateTree()
    {
        $this->aggregateElements();
        foreach ($this->elements as $element) {
            if ($this->parent instanceof Node) {
                if ($element instanceof IAggregatable) {
                    $this->getParent()->addElement($element);
                }
            }
        }
    }

    /**
     * @return void
     */
    public function aggregateElements()
    {
        $sortedElements = array();

        foreach ($this->elements as $key => $element) {
            if ($element instanceof IAggregatable) {
                $index = $element->getAggregateKey();

                if (isset($sortedElements[$index])) {
                    $sortedElements[$index] = $sortedElements[$index]
                            ->aggregate($element);
                } else {
                    $sortedElements[$index] = $element;
                }
                
                unset($this->elements[$key]);
            }
        }
        
        foreach($sortedElements as $element) {
          $this->elements[] = $element;
        }
    }
}
