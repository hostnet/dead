<?php
require_once "common/Settings.php";

class TaskRunner
{
      const SETTINGS_ERROR = 2;
      const AUTOLOAD_ERROR = 3;
      const GENERAL_ERROR = 1;

    static public function autoload($name)
    {
        $filename = "task/$name.php";
        if (file_exists($filename)) {
            require_once $filename;
        } elseif (file_exists("phar://dead/$filename")) {
            require_once "phar://dead/$filename";
        }
    }

    public static function main()
    {
        if (PHP_SAPI == "cli") {
            $out = fopen("php://stderr","w");
        } else {
            $out = fopen("php://output","w");
        }
        
        try {
            $settings = Settings::instance();
        } catch (Exception $e) {
            fwrite($out,$e->getMessage() . PHP_EOL);
            fwrite($out,$e->getTraceAsString() . PHP_EOL);
            fclose($out);
            exit(self::SETTINGS_ERROR);
        }

        try {

            //Add autoload functionallity for Task classes
            spl_autoload_register("TaskRunner::autoload");

            if ($settings->getCommandName() === false) {
                $settings->displayUsage();
            }

            $class = ucfirst($settings->getCommandName()) . "Task";

            try {
                $task = new $class();
            } catch (Exception $e) { 
              fwrite($out,"Subcommand misconfiguration in args.yml" . PHP_EOL);
              fwrite($out, $e->getMessage() . PHP_EOL);
              fwrite($out, $e->getTraceAsString() . PHP_EOL);
              fclose($out);
              exit(self::AUTOLOAD_ERROR);
            }

            $task->run();
        } catch (Exception $e) {
            $m = $e->getMessage();
            fwrite($out,"Error occured with message: $m" . PHP_EOL);
            fclose($out);
            exit(self::GENERAL_ERROR);
        }
        
        fclose($out);
    }
}
