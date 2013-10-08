<?php

require_once __DIR__ .'/../vendor/autoload.php';

if (PHP_SAPI == "cli") {
    $time_start = microtime(true);

    // This is our function to handle
    // assert failures
    function assert_failure()
    {
        debug_print_backtrace();
        trigger_error("Assert Failed", E_USER_ERROR);
    }

    // Set our assert options
    assert_options(ASSERT_ACTIVE, true);
    assert_options(ASSERT_BAIL, true);
    assert_options(ASSERT_WARNING, false);
    assert_options(ASSERT_CALLBACK, 'assert_failure');

    // Set debug options
    ini_set('display_errors', '1');
    error_reporting(E_ALL | E_STRICT);
}

set_include_path(get_include_path() . PATH_SEPARATOR .  __DIR__);

if (PHP_SAPI == "cli") {
    TaskRunner::main();

    $settings = Settings::instance();
    if ($settings->getOption("information") == true) {
        $time_end = microtime(true);

        $time = str_pad(sprintf("%10.5f seconds", $time_end - $time_start), 18);
        $peak = str_pad(
                sprintf("%10.5f MiB", memory_get_peak_usage() / 1024 / 1024),
                18);
        $mem = str_pad(
                sprintf("%10.5f MiB", memory_get_usage() / 1024 / 1024), 18);

        fwrite(STDERR, "╔═══════════════════════════════════╗" . PHP_EOL);
        fwrite(STDERR, "║ time:         $time  ║" . PHP_EOL);
        fwrite(STDERR, "║ peak memory:  $peak  ║" . PHP_EOL);
        fwrite(STDERR, "║ memory:       $mem  ║" . PHP_EOL);
        fwrite(STDERR, "╚═══════════════════════════════════╝" . PHP_EOL);
    }
}
