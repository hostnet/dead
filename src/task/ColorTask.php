<?php

class ColorTask extends AbstractPDOTask {

  private $output;
  private $path;
  
	public function __construct() {
	  parent::__construct();
	  
	  $settings = Settings::instance();
	  
	  //Load output file
	  $this->output = $settings->getOption("output");
	  if($this->output == "-") {
	    $this->output = "php://stdout";
	  }
	  
	  $this->path = $settings->getCommand()->getOption("workspace");
	   
	}
	public function run() {
		$factory = new PDOTreeFactory ($this->getDb());
		$factory->query();
		$tree = $factory->produceTree();
		
		$visitor = new EclipseColorVisitor($this->output,$this->path);
		$tree->acceptDepthFirst($visitor);
	}
}
