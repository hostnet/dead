<?php

class Gnuplot
{
  const BUFFER_LENGTH = 1024;
  
  private static $gnuplotCommand = "gnuplot";

  public static function plot($commands, array $data, $stderr = true)
  {
    $output = "";
    $errors = "";
    
    $spec = array(0 => array("pipe", "r"), 1 => array("pipe", "w"), 2 => array("pipe", "w"));
    $proc = proc_open(self::$gnuplotCommand, $spec, $pipe);
    if(is_resource($proc)) {
      fwrite($pipe[0], $commands);
      
      foreach($data as $set) {
        fwrite($pipe[0], $set);
        fwrite($pipe[0], "\n\ne\n");
      }
      fclose($pipe[0]);
      
      $output = stream_get_contents($pipe[1]);
      fclose($pipe[1]);
      $errors = stream_get_contents($pipe[2]);
      fclose($pipe[2]);    
      
      proc_close($proc);      
    }
    
    if($stderr) {
      $err = fopen("php://stderr","w");
      fwrite($err, $errors);
      fclose($err);
    } else {
      $output = $errors . $output;
    }
    
    return $output;
  }
}

?>
