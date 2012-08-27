<?php
require_once "AbstractPDOTask.php";
require_once "tree/PDOTreeMapFactory.php";
require_once "visitor/JsonVisitor.php";
require_once "common/Settings.php";

class JsonTask extends AbstractPDOTask
{
    private $path;

    public function __construct($path = null)
    {
        parent::__construct();
        if ($path === null) {
            $settings = Settings::instance();
            $this->path = $settings->getCommand()->getArgument("path");
        } else {
            $this->path = $path;
        }
    }
    /**
     * @see ITask::run()
     */
    public function run()
    {
        $factory = new PDOTreeMapFactory($this->getDb());
        $factory->query($this->path);
        $list = $factory->produceList();

        $visitor = new JsonVisitor();
        foreach ($list as $node) {
            $node->accept($visitor);
        }

        echo $visitor->produceJson($this->path);

    }

}
