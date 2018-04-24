<?php
/**
 * @copyright 2012-2018 Hostnet B.V.
 */
declare(strict_types=1);

define("MIB", 1048575);
define("FIELD", 255);

class GitVisitor extends AbstractVersioningVisitor
{
    /**
     * @var array[string][]Commit
     */
    private $commits = null;

    /**
     * @var string
     */
    private $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    protected function getCommits($file)
    {
        // Lookup all commits if still needed
        if ($this->commits === null) {
            $this->commits = $this->getAllCommitsRecursive();
        }

        // Do not do realpath for a bare repository
        if (file_exists($file)) {
            $file = realpath($file);
        }

        // Check for existance of the file
        if (isset($this->commits[$file])) {
            return $this->commits[$file];
        }

        echo "$file not found in Git repository.\n";

        return [];
    }

    private function getAllCommitsRecursive($max = 1)
    {
        $link           = trim(shell_exec("cd $this->path  && git rev-parse --show-cdup"));
        $git_root       = realpath($this->path . "/" . $link);
        $start_path     = str_replace($git_root, "", realpath($this->path));
        $pretty         = "format:%H%x00%ct%x00%aN%x00%s";
        $cmd            = "git log -m --raw --pretty=$pretty --name-only -z $start_path";
        $descriptorspec = [1 => ["pipe", "w"]];
        $pipes          = [];
        $files          = [];
        $next_date      = true;
        $process        =
            proc_open($cmd, $descriptorspec, $pipes, realpath($this->path));

        if (is_resource($process)) {
            while (!feof($pipes[1])) {
                if ($next_date == true) {
                    $id        = stream_get_line($pipes[1], FIELD, "\0");
                    $date      = stream_get_line($pipes[1], FIELD, "\0");
                    $author    = stream_get_line($pipes[1], FIELD, "\0");
                    $message   = stream_get_line($pipes[1], MIB, "\n");
                    $next_date = false;
                } else {
                    $file      = stream_get_line($pipes[1], FIELD, "\0");
                    $full_path = $git_root . DIRECTORY_SEPARATOR . $file;
                    if (empty($file)) {
                        $next_date = true;
                    } elseif (!isset($files[$full_path])
                        || (count($files[$full_path]) < $max)) {
                        $files[$full_path][] = new Commit($id, $author, new DateTime("@$date"), $message);
                    }
                }
            }
        }

        return $files;
    }

    public function visitFunctionName(FileFunction $file_function)
    {
        // TODO: Implement visitFunctionName() method.
    }
}
