<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

class YmlCommandLine extends Console_CommandLine
{
    /**
     *
     * @param string $filename
     * @return Console_CommandLine
     * @throws Exception
     */

    public static function fromYmlFile($filename, $config = null)
    {
        $yml = file_get_contents($filename, true);

        if ($config) {
            $config = file_get_contents($config, true);
        }

        if ($yml !== false) {
            return self::fromYamlString($yml, $config);
        } else {
            throw new Exception("Could not read yaml file");
        }
    }

    /**
     * Read Console_CommandLine_Command from yml strings, one with all arguments
     * and one with configuration options to overwrite default values set in the
     * first one.
     *
     * @param string $yml
     * @param string $config_yml
     * @return Console_CommandLine_Command
     * @throws Exception
     */

    public static function fromYamlString($yml, $config_yml = null)
    {
        $parser = new SfYamlParser();

        $yml_tree = $parser->parse($yml);

        if ($config_yml) {
            $yml_config_tree = $parser->parse($config_yml);
            self::mergeDefaultsIntoYml($yml_tree, $yml_config_tree);
        }

        $cmd = self::ymlToCmd($yml_tree);
        $cmd = self::ymlParseOptions($yml_tree, $cmd);
        $cmd = self::ymlParseArguments($yml_tree, $cmd);
        $cmd = self::ymlParseCommands($yml_tree, $cmd);

        return $cmd;
    }

    private static function mergeDefaultsIntoYml(&$yml, $defaults)
    {
        foreach ($defaults as $key => $default) {
            if (is_array($default) && isset($yml[$key])) {
                self::mergeDefaultsIntoYml($yml[$key], $default);
            } else {
                $yml[$key]['default'] = $default;
            }
        }
    }

    /**
     *
     * @param array $ymltree
     * @param Console_CommandLine $cmd
     * @return Console_CommandLine
     */

    private static function ymlParseCommands(array $ymltree, Console_CommandLine $cmd)
    {
        if (isset($ymltree['commands']) && is_array($ymltree['commands'])) {
            foreach ($ymltree['commands'] as $name => $command) {
                $params  = array_diff_key($command, array('arguments' => null, 'options' => null));
                $sub_cmd = $cmd->addCommand($name, $params);
                self::ymlParseOptions($command, $sub_cmd);
                self::ymlParseArguments($command, $sub_cmd);
                self::ymlParseCommands($command, $sub_cmd);
            }
        }

        return $cmd;
    }

    /**
     *
     * @param array $ymltree
     * @param Console_CommandLine $cmd
     * @return Console_CommandLine
     */

    private static function ymlParseArguments(array $ymltree, Console_CommandLine $cmd)
    {
        if (isset($ymltree['arguments']) && is_array($ymltree['arguments'])) {
            foreach ($ymltree['arguments'] as $name => $argument) {
                $cmd->addArgument($name, $argument);
            }
        }

        return $cmd;
    }

    /**
     *
     * @param array $ymltree
     * @param Console_CommandLine $cmd
     * @return Console_CommandLine
     */

    private static function ymlParseOptions(array $ymltree, Console_CommandLine $cmd)
    {
        if (isset($ymltree['options']) && is_array($ymltree['options'])) {
            foreach ($ymltree['options'] as $name => $option) {
                $cmd->addOption($name, $option);
            }
        }

        return $cmd;
    }

    /**
     *
     * @param array $ymltree
     * @return Command_Line
     * @throws Exception
     */

    private static function ymlToCmd(array $ymltree)
    {
        if (isset($ymltree['command'])) {
            $command = $ymltree['command'];
        } else {
            throw new Exception("No Command section found in yaml file");
        }

        return new Console_CommandLine($command);
    }
}
