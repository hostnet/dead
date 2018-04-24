<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

class Settings
{
    /**
     * @var Console_CommandLine_Result
     */
    private $settings = null;

    /**
     * @var Console_CommandLine
     */
    private $parser;

    /**
     * @var Settings
     */
    private static $instance = null;

    /**
     * @param Settings $settings
     * @param null $parser
     */

    private function __construct(&$settings = null, &$parser = null)
    {
        //Singleton
        if ($settings === null) {
            try {
                $phardir = realpath(dirname(Phar::running(false)));
                if (file_exists('/etc/dead.conf')) {
                    $config_file = '/etc/dead.conf';
                } elseif (isset($_SERVER['HOME']) && file_exists($_SERVER['HOME'] . '/.deadrc')) {
                    $config_file = $_SERVER['HOME'] . '/.deadrc';
                    echo "found $config_file";
                } elseif (file_exists("$phardir/config.yml")) {
                    $config_file = "$phardir/config.yml";
                } else {
                    $config_file = stream_resolve_include_path("config.yml");
                }
                $this->parser   = YmlCommandLine::fromYmlFile("args.yml", $config_file);
                $this->settings = $this->parser->parse();
            } catch (\Throwable $exc) {
                $this->parser->displayError($exc->getMessage());
            }
        } else {
            $this->settings = &$settings;
            $this->parser   = &$parser;
        }
    }

    public function displayUsage()
    {
        $this->parser->displayUsage();
    }

    /**
     * @return Settings
     */

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new Settings();
        }

        return self::$instance;
    }

    public function getCommandName()
    {
        if (!$this->settings instanceof Console_CommandLine_Result) {
            print_r($this->settings);
            throw new SettingsException("(sub)command");
        }

        return $this->settings->command_name;
    }

    public function getCommand()
    {
        $command = $this->getCommandName();
        if ($command !== "") {
            return new Settings(
                $this->settings->command,
                $this->parser->commands[$this->getCommandName()]
            );
        }

        throw new SettingsException("Sub Command");
    }

    public function getOption($name)
    {
        if (isset($this->settings->options[$name])) {
            return $this->settings->options[$name];
        }

        return false;
    }

    public function getArgument($name)
    {
        if (isset($this->settings->args[$name])) {
            return $this->settings->args[$name];
        }

        throw new SettingsException($name);
    }
}
