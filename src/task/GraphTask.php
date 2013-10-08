<?php
class GraphTask implements ITask {

  public function run()
  {
    $settings = Settings::instance()->getCommand();
    
    switch ($settings->getCommandName()) {
        case "saturation":
          $t = new SaturationGraphTask();
          break;  
      default:
          $settings->displayUsage();
    }
    
    $t->run();
  }

  
}