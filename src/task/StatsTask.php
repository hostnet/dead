<?php
require_once "AbstractPDOTask.php";
require_once "tree/PDOTreeFactory.php";
require_once "visitor/PDOCacheTreeVisitor.php";

class StatsTask extends AbstractPDOTask
{

  private $output;
  private $path;
  private $date_format;
  private $format;
  private $utc;
  private $latex_prefix;

  public function __construct()
  {
    parent::__construct();

    $settings = Settings::instance();

    //Load output file
    $this->output = $settings->getOption("output");
    if($this->output == "-") {
      $this->output = "php://stdout";
    }

    $this->path = $settings->getCommand()->getOption("workspace");
    $this->date_format = $settings->getCommand()->getOption("date_format");
    $this->format = $settings->getCommand()->getOption("format");
    $this->utc = $settings->getCommand()->getOption("utc");
    $this->latex_prefix = $settings->getCommand()->getOption("latex_prefix");

  }

  public function run()
  {
    $factory = new PDOTreeFactory($this->getDb());
    $factory->query();
    $tree = $factory->produceTree();

    $visitor = new PDOCacheTreeVisitor();
    $tree->acceptDepthFirst($visitor);

    $data = current(array_slice($visitor->getData(), -1));

    $data['pct_dead'] = round($data['dead_count'] / $data['file_count'] * 100, 2);
    $data['pct_alive'] = 100 - $data['pct_dead'];

    foreach(array_keys($data) as $key) {
      $data[$key] =
        $this->transform($data[$key], AbstractPDOTask::NO_ESCAPE, $this->date_format, $this->utc);
    }

    switch($this->format) {
      case "latex":
        $this->latex($data);
        break;
      default:
        $this->text($data);
    }
  }

  private function parseNumberToText($string)
  {
    $string = str_replace("1", "One", $string);
    $string = str_replace("2", "Two", $string);
    $string = str_replace("3", "Three", $string);
    $string = str_replace("4", "Four", $string);
    $string = str_replace("5", "Five", $string);
    $string = str_replace("6", "Six", $string);
    $string = str_replace("7", "Seven", $string);
    $string = str_replace("8", "Eight", $string);
    $string = str_replace("9", "Nine", $string);
    $string = str_replace("0", "Zero", $string);
    return $string;
  }

  private function latex($data)
  {
    $prefix = $this->latex_prefix . ucfirst($this->parseNumberToText($this->getTable()));
    echo <<<EOD
\\newcommand{\\${prefix}PctAlive}{{$data['pct_alive']}}
\\newcommand{\\${prefix}PctDead}{{$data['pct_dead']}}
\\newcommand{\\${prefix}DeadCount}{{$data['dead_count']}}
\\newcommand{\\${prefix}FileCount}{{$data['file_count']}}
\\newcommand{\\${prefix}Count}{{$data['count']}}
\\newcommand{\\${prefix}FirstHit}{{$data['first_hit']}}
\\newcommand{\\${prefix}LastHit}{{$data['last_hit']}}
\\newcommand{\\${prefix}ChangedAt}{{$data['changed_at']}}

EOD;
  }

  private function text($data)
  {
    foreach(array_keys($data) as $key) {
      $data[$key] = str_pad($data[$key], 20, " ", STR_PAD_LEFT);
    }
    echo <<<EOD
percentage alive files: $data[pct_alive]
percentage dead files:  $data[pct_dead]
number of dead files:   $data[dead_count]
number of files:        $data[file_count]
number of inclusions:   $data[count]
first inclusion:        $data[first_hit]
last inclusion:         $data[last_hit]
last changed:           $data[changed_at]

EOD;
  }
}
