<?php
/**
 * @copyright 2018 Hostnet B.V.
 */
declare(strict_types=1);

class FunctionPathsFactory
{
    /**
     * @param Node $file
     * @return array
     */
    private function produceTokens(Node $file): array
    {
        $file_contents = file_get_contents($file->getLocation());
        $tokens        = token_get_all($file_contents);

        return [
            "full_path" => $file->getFullPath(),
            "tokens"    => $tokens,
        ];
    }

    /**
     * @param array $file
     * @return FileFunction[]
     */
    private function produceFunctionPaths(array $file): array
    {
        $function_paths = [];

        // Namespace and class values will be reset for every file.
        $namespace = '';
        // There can be multiple classes in one file, so expect this value to change
        // TODO detect when leaving a class. (counting curly brackets?)
        $class = '';
        foreach ($file['tokens'] as $token_index => $token) {
            switch ($token[0]) {
                case T_CLASS:
                    $class = $file['tokens'][$token_index + 2][1];
                    break;

                case T_NAMESPACE:
                    $i = $token_index;
                    while ($file['tokens'][$i] !== ';') {
                        if ($file['tokens'][$i][0] === T_NS_SEPARATOR ||
                            $file['tokens'][$i][0] === T_STRING) {
                            $namespace .= $file['tokens'][$i][1];
                        }
                        $i += 1;
                    }
                    break;

                case T_FUNCTION:
                    $i                   = $token_index;
                    $function_name_token = $file['tokens'][$i];
                    while (($function_name_token[0] ?? 0) !== T_STRING) {
                        $function_name_token = $file['tokens'][$i++];
                    }
                    $function_name    = $function_name_token[1];
                    $function_paths[] = $this->generateFileFunction(
                        $file['full_path'],
                        $namespace,
                        $class,
                        $function_name
                    );
                    break;
                default:
                    break;
            }
        }

        return $function_paths;
    }

    private function generateFileFunction(
        string $full_path,
        string $namespace,
        string $class,
        string $function_name
    ): FileFunction {
        $fully_qualified_namespace = $full_path . "::";

        if (!empty($namespace)) {
            $fully_qualified_namespace .= $namespace;
        }

        if (!empty($namespace) && !empty($class)) {
            $fully_qualified_namespace .= "\\";
        }

        if (!empty($class)) {
            $fully_qualified_namespace .= $class . "::";
        }

        return new FileFunction($fully_qualified_namespace . $function_name);
    }

    /**
     * @param Node $file
     * @return FileFunction[]
     */
    public function produceList(Node $file): array
    {
        return $this->produceFunctionPaths($this->produceTokens($file));
    }
}
