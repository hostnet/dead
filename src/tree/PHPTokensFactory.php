<?php

class PHPTokensFactory
{

    private $files;
    private $tokensPerFile;
    private $functionPaths;

    function __construct($files)
    {
        $this->files = $files;
        $this->tokensPerFile = [];
        $this->functionPaths = [];
    }

    private function produceTokens($files): array
    {
        $tokensPerFile = [];
        foreach ($files as $index => $file) {
            $file_contents = file_get_contents($file->getLocation());
            $tokens = token_get_all($file_contents);
            $tokensPerFile[$index]["location"] = $file->getLocation();
            $tokensPerFile[$index]["tokens"] = $tokens;
        }
        return $tokensPerFile;
    }

    private function produceFunctionPaths($tokensPerFile): array
    {
        $functionsPaths = [];
        foreach ($tokensPerFile as $file) {
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
                            if ($file["tokens"][$i][0] === T_WHITESPACE) {
                                $i += 1;
                                continue;
                            }
                            $namespace .= $file["tokens"][$i][1];
                            $i += 1;
                        }
                        break;

                    case T_FUNCTION:
                        $functionName = $file["tokens"][$token_index + 2][1];

                        $functionPath = $file["location"] .
                            (empty($namespace) ? "" : "/" . $namespace) .
                            (empty($class) ? "" : "/" . $class) .
                            "::" . $functionName;

                        array_push($functionsPaths, $functionPath);
                        break;
                    default:
                        break;
                }
            }
        }

        return $functionsPaths;

    }

    public function produceList(): array
    {
        $this->tokensPerFile = $this->produceTokens($this->files);
        $this->functionPaths = $this->produceFunctionPaths($this->tokensPerFile);
        return $this->functionPaths;
    }

    /**
     * @return array files
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @param mixed $files
     */
    public function setFiles($files): void
    {
        $this->files = $files;
    }


}