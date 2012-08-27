<?php
/**
 * 
 * @author Hidde Boomsma <hidde@hostnet.nl>
 *
 */
require_once "AbstractPDOTask.php";
require_once "common/Settings.php";
require_once "common/Gnuplot.php";

class SaturationGraphTask extends AbstractPDOTask
{

  private $output = "php://output";
  private $width;
  private $height;
  private $path;
  private $tables;
  private $scale;

  /**
   * 
   */

  public function __construct(array $tables = null, $width = null, $height = null, $path = null,
    $scale = null)
  {
    parent::__construct();
    $settings = Settings::instance();

    if($height === null) {
      $this->height = $settings->getCommand()->getOption("height");
    } else {
      $this->height = $height;
    }

    if($width === null) {
      $this->width = $settings->getCommand()->getOption("width");
    } else {
      $this->width = $width;
    }

    if($path === null) {
      //$this->path = $settings->getCommand()->getCommand()->getOption("path");
    } else {
      $this->path = $path;
    }

    $output = $settings->getOption("output");
    if($output !== "-") {
      $this->output = $output;
    }

    if($tables === null) {
    	try{
        $tables = $settings->getCommand()->getCommand()->getArgument("tables");
    	} catch (Exception $e) {
    		$tables = array();
    	}
      if(count($tables) > 0) {
        $this->tables = $tables;
      } else {
        $this->tables = array($this->getTable());
      }
    } else {
      $this->tables = $tables;
    }
    

    if($scale === null) {
      $this->scale = $settings->getCommand()->getCommand()->getOption("scale");
    } else {
      $this->scale = $scale;
    }
  }

  public function run()
  {
    $data = array();
    foreach($this->tables as $table) {
      try {
      	  $data[] = $this->fetchDataSet($this->path, $table, $this->scale);
      } catch (Exception $e) {
      	print_r($e);
      }
    }
    $commands = $this->plotfile($this->width, $this->height, $this->tables);
    $out = fopen($this->output, "w");
    fwrite($out, Gnuplot::plot($commands, $data));
    fclose($out);
  }

  private function fetchDataSet($path, $table, $scale)
  {
    $data = "";
    $accumulated = 0;
    $db = $this->getDb();
    $path = $db->quote("$path%");
    $sql =
      "SELECT unix_timestamp(first_hit) as ts_first_hit, 
         count(*) as hit_count, 
         added_at, 
         (select unix_timestamp(min(first_hit)) FROM `$table`) as min_first_hit, 
         (select count(*) FROM `$table`) as file_count,
         (select min(added_at) FROM `$table`) as min_added_at
       FROM `$table`
       WHERE first_hit IS NOT NULL
       AND file LIKE $path
       GROUP BY unix_timestamp(first_hit)
       HAVING added_at IS NULL OR added_at = min_added_at 
       ";
    $statement = $db->query($sql);
    $row = array();
    while(($row = $statement->fetch(PDO::FETCH_NUM)) == true) {
      $accumulated += $row[1];
      if($this->scale === true) {
        $days = $row[3];
        $time = $row[0] - $days;
        $pct = (int) ($accumulated / $row[4] * 100);
        $data .= "$time $pct\n";
      } else {
        $data .= "$row[0] $accumulated\n";
      }
    }

    if($scale) {
      $data .= time() - $days . " " . $pct . "\n";
    } else {
      $data .= time() . " " . $accumulated . "\n";
    }
    return $data;
  }

  private function plotfile($width, $height, $tables)
  {
    $first = true;
    $gnuplot =
      <<<EOD
reset
set timefmt '%s'
set grid lt 0 lw 1
set key bottom box
set xtics autofreq

set style line 4 lt rgb "orange" lw 1
set style line 5 lt rgb "#777777" lw 1
set style line 6 lt rgb "#AA00AA" lw 1
set style line 7 lt rgb "#00DDDD" lw 1

set terminal svg size $width,$height fixed enhanced fname 'arial'  fsize 11 butt solid

EOD;
    if($this->scale) {
      $gnuplot .= "set xlabel 'Time (days)' offset 0,-1\n";
      $gnuplot .= "set ylabel 'Files used (percentage)'\n";
    } else {
      $gnuplot .= "set xlabel 'Time' offset 0,-1\n";
      $gnuplot .= "set ylabel 'Files used'\n";
      $gnuplot .= "set xdata time\n";
      $gnuplot .= "set format x '%d %b'\n";
      $gnuplot .= "set nokey\n";

    }
    $i = 0;
    foreach($tables as $table) {
      if($first === true) {
        $first = false;
        $gnuplot .= "plot ";
      } else {
        $gnuplot .= ",";
      }
      $i++;

      if($this->scale) {
        $gnuplot .=
          " \"-\" using ($1/3600/24):2 with linespoints ls $i ps .75 pt 1 title \"$table\"";
      } else {
        $gnuplot .= " \"-\" using 1:2 with linespoints ls $i ps .75 pt 1 title \"$table\"";
      }
    }
    $gnuplot .= "\n";
    return $gnuplot;
  }

}
?>
