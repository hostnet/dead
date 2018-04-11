<?php
/**
 * @copyright 2018 Hostnet B.V.
 */
declare(strict_types = 1);

class PHPTokensFactory
{

    private $files;

    /**
     * PHPTokensFactory constructor.
     * $files is an array of Node objects.
     * @param $files
     */
    public function __construct(array $files)
    {
        $this->files = $files;
    }

    /**
     * $files is an array of Node objects.
     * @param $files
     * @return array
     */
    private function produceTokens(array $files): array
    {
        $tokens_per_file = [];
        foreach ($files as $index => $file) {
            $file_contents                       = file_get_contents($file->getLocation());
            $tokens                              = token_get_all($file_contents);
            $tokens_per_file[$index]["location"] = $file->getLocation();
            $tokens_per_file[$index]["tokens"]   = $tokens;
        }

        return $tokens_per_file;
    }

    /**
     * @param $tokens_per_file
     * @return array
     */
    private function produceFunctionPaths($tokens_per_file): array
    {
        $function_paths = [];
        foreach ($tokens_per_file as $file) {
            // Namespace and class values will be reset for every file.
            $namespace = "";
            // There can be multiple classes in one file, so expect this value to change
            // TODO detect when leaving a class. (counting curly brackets?)
            $class = "";
            foreach ($file["tokens"] as $token_index => $token) {
                switch ($token[0]) {
                    case T_CLASS:
                        $class = $file["tokens"][$token_index + 2][1];
                        break;

                    case T_NAMESPACE:
                        $i = $token_index;
                        while ($file["tokens"][$i] !== ";") {
                            if ($file["tokens"][$i][0] === T_NS_SEPARATOR ||
                                $file["tokens"][$i][0] === T_STRING) {
                                $namespace .= $file["tokens"][$i][1];
                            }
                            $i += 1;
                        }
                        break;

                    case T_FUNCTION:
                        $function_name = $file["tokens"][$token_index + 2][1];

                        $function_path = $file["location"].
                            (empty($namespace) ? "" : "/" . $namespace) .
                            (empty($class) ? "" : "/" . $class) .
                            "::".$function_name;

                        array_push($function_paths, $function_path);
                        break;
                    default:
                        break;
                }
            }
        }

        return $function_paths;
    }

    /**
     * @return array
     */
    public function produceList(): array
    {
        return $this->produceFunctionPaths($this->produceTokens($this->files));
    }

    /**
     * @return array of Node objects
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @param array $files
     */
    public function setFiles(array $files): void
    {
        $this->files = $files;
    }
}
