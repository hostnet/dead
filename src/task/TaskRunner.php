<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

class TaskRunner
{
    const SETTINGS_ERROR = 2;
    const AUTOLOAD_ERROR = 3;
    const GENERAL_ERROR  = 1;

    public static function autoload($name)
    {
        $filename = "task/$name.php";
        file_exists($filename) || file_exists("phar://dead/$filename");
    }

    public static function main()
    {
        if (PHP_SAPI == "cli") {
            $out = fopen("php://stderr", "w");
        } else {
            $out = fopen("php://output", "w");
        }

        try {
            $settings = Settings::instance();
        } catch (\Throwable $e) {
            fwrite($out, $e->getMessage() . PHP_EOL);
            fwrite($out, $e->getTraceAsString() . PHP_EOL);
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
            } catch (\Throwable $e) {
                fwrite($out, "Subcommand misconfiguration in args.yml" . PHP_EOL);
                fwrite($out, $e->getMessage() . PHP_EOL);
                fwrite($out, $e->getTraceAsString() . PHP_EOL);
                fclose($out);
                exit(self::AUTOLOAD_ERROR);
            }

            $task->run();
        } catch (\Throwable $e) {
            $m = $e->getMessage();
            fwrite($out, "Error occured with message: $m" . PHP_EOL);
            fclose($out);
            exit(self::GENERAL_ERROR);
        }

        fclose($out);
    }
}
