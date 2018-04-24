<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

class AstFilesVisitor extends AbstractNodeElementVisitorInterface
{
    public function __construct()
    {
    }

    public function visitAst(Ast &$ast)
    {
        $this->parseAst($ast);
    }

    private function parseAst(Ast $ast)
    {
        $xml = new SimpleXMLElement(
            $ast->getAst(),
            null,
            false,
            "http://www.phpcompiler.org/phc-1.0"
        );
        $this->registerNamespaceAst($xml);
        $classes = $xml
            ->xpath(
                "/ast:AST_php_script/ast:AST_class_def_list/ast:AST_class_def"
            );
        foreach ($classes as $class) {
            $this->parseClass($class);
        }
    }

    private function registerNamespaceAst(SimpleXMLElement &$xml)
    {
        $xml
            ->registerXpathNamespace(
                "ast",
                "http://www.phpcompiler.org/phc-1.0"
            );
    }

    private function parseClass(SimpleXMLElement $class)
    {
        $this->registerNamespaceAst($class);
        $value = ($class->xpath("ast:Token_class_name/ast:value"));
        $name  = (string) $value[0];
        $base  = isset($value[1]) ? (string) $value[1] : "";

        echo $name;
        if ($base) {
            echo " extends $base";
        }
        echo "\n";

        $methods = $class->xpath("ast:AST_member_list/ast:AST_method");
        foreach ($methods as $method) {
            $this->parseMethod($method);
        }
    }

    private function parseMethod(SimpleXMLElement $method)
    {
        $this->registerNamespaceAst($method);

        $flags     = $method
            ->xpath("ast:AST_signature/ast:AST_method_mod/ast:bool");
        $public    = $flags[0] == true;
        $protected = $flags[1] == true;
        $private   = $flags[2] == true;
        $static    = $flags[3] == true;
        $abstract  = $flags[4] == true;
        $final     = $flags[5] == true;

        $name = (string) current(
            $method
                ->xpath(
                    "ast:AST_signature/ast:Token_method_name/ast:value"
                )
        );
        echo "$name\n";

        $assignments = [];

        $invocations = $method
            ->xpath("ast:AST_statement_list//ast:AST_method_invocation");

        foreach ($invocations as $invocation) {
            $this->parseInvocation($invocation);
        }
        echo "\n";
    }

    private function parseInvocation(SimpleXMLElement $invocation)
    {
        $dynamic           = false;
        $t                 = $invocation->AST_variable->Token_variable_name;
        $invocation_var    = $t ? $t->value : null;
        $invocation_class  = $invocation->Token_class_name->value;
        $invocation_method = $invocation->Token_method_name->value;
        echo "  [$invocation_var,$invocation_class,$invocation_method]\n";
        if ($invocation_var && $invocation_var != 'this') {
            $dynamic = true;
        }
        echo $dynamic ? 'dynamic' : 'not dynamic', PHP_EOL;
    }

    public function visitFunctionName(FileFunction $file_function)
    {
        // TODO: Implement visitFunctionName() method.
    }
}
