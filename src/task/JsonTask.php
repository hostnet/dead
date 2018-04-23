<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

class JsonTask extends AbstractPdoTaskInterface
{
    private $path;

    public function __construct($path = null)
    {
        parent::__construct();
        if ($path === null) {
            $settings   = Settings::instance();
            $this->path = $settings->getCommand()->getArgument("path");
        } else {
            $this->path = $path;
        }
    }

    /**
     * @see TaskInterface::run()
     */
    public function run()
    {
        $factory = new PdoTreeMapFactory($this->getDb());
        $factory->query($this->path);
        $list = $factory->produceList();

        $visitor = new JsonVisitor();
        foreach ($list as $node) {
            $node->accept($visitor);
        }

        echo $visitor->produceJson($this->path);
    }
}
