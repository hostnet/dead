<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

class Gnuplot
{
    const BUFFER_LENGTH = 1024;

    private static $gnuplot_command = "gnuplot";

    public static function plot($commands, array $data, $stderr = true)
    {
        $output = "";
        $errors = "";

        $spec = [0 => ["pipe", "r"], 1 => ["pipe", "w"], 2 => ["pipe", "w"]];
        $proc = proc_open(self::$gnuplot_command, $spec, $pipe);
        if (is_resource($proc)) {
            fwrite($pipe[0], $commands);

            foreach ($data as $set) {
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

        if ($stderr) {
            $err = fopen("php://stderr", "w");
            fwrite($err, $errors);
            fclose($err);
        } else {
            $output = $errors . $output;
        }

        return $output;
    }
}
