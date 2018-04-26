<?php
/**
 * @copyright 2018 Hostnet B.V.
 */
declare(strict_types=1);

class PrimeTask extends AbstractPdoTaskInterface
{
    // commandline or config
    private $path;
    private $vcs;

    const NONE = 'none';
    const SVN  = 'svn';
    const GIT  = 'git';

    public function __construct()
    {
        parent::__construct();
        $settings = Settings::instance();

        $path = $settings->getCommand()->getArgument("path");
        $path = $this->fixTrailingDirectorySeperator($path);

        $prefix = $settings->getCommand()->getOption("prefix");
        $prefix = $this->fixTrailingDirectorySeperator($prefix);

        $this->path   = $path;
        $this->prefix = $prefix;
        $this->vcs    = $settings->getCommand()->getOption("vcs");
    }

    /**
     * Make sure the path ends with a separator (/ on linux, mac and unix)
     * @param $path string the path that sould be fixed
     * @return string
     */
    private function fixTrailingDirectorySeperator($path)
    {
        if (substr($path, -1, 1) != DIRECTORY_SEPARATOR) {
            $path .= DIRECTORY_SEPARATOR;
        }

        return $path;
    }

    /**
     * @param $list array[]Node
     * @return array[string]PrimeData
     */
    private function getPrimeData(array $list, $prefix = null)
    {
        // Create PrimeVisitor for fetching data
        $visitor = new PrimeVisitor();
        $visitor->setPrefix($prefix);

        foreach (array_keys($list) as $key) {
            $list[$key]->accept($visitor);
            $visitor->resetFunctions();
        }

        return $visitor->getPrimeData();
    }

    /**
     * @param Node[] $compare_from
     * @param Node[] $compare_against
     * @return String[]
     */
    private function getFileFunctionDifference(array $compare_from, array $compare_against): array
    {
        $result = [];
        foreach ($compare_from as $key => $value) {
            foreach ($value->getFileFunctions() as $file_function) {
                $is_in_compare_against = in_array($file_function, $compare_against[$key]->getFileFunctions());
                if ($is_in_compare_against) {
                    continue;
                }

                $result[] = $file_function;
            }
        }

        return $result;
    }

    private function &addVersioning(&$list, NodeElementVisitorInterface $visitor)
    {
        foreach (array_keys($list) as $key) {
            $list[$key]->accept($visitor);
        }

        return $list;
    }

    private function getDbNodes()
    {
        // Read all files from the databse
        $factory = new PdoTreeFactory($this->getDb());
        $factory->query(PdoTreeFactory::ALL_FILES);
        $list = $factory->produceList();

        return $list;
    }

    public function run()
    {
        $local_nodes    = null;
        $database_nodes = null;

        if ($this->vcs == self::SVN) {
            /*
             * SVN
             */
            $local_nodes = (new FileTreeFactory())->scan($this->path)->produceList();
            echo "read all local_nodes from disk into list\n";
            $visitor = new SubversionVisitor($this->path);
            $this->addVersioning($local_nodes, $visitor);
            echo "added versioning info from svn to disk local_nodes list\n";
        } elseif ($this->vcs == self::GIT) {
            /*
             * GIT
             */
            $local_nodes = (new GitFileTreeFactory())->scan($this->path)->produceList();
            echo "read all local_nodes from disk into list\n";
            $visitor = new GitVisitor($this->path);
            $this->addVersioning($local_nodes, $visitor);
            echo "added versioning info from git to disk local_nodes list\n";
        } elseif ($this->vcs == self::NONE) {
            /*
             * NONE
             */
            $local_nodes = (new FileTreeFactory())->scan($this->path)->produceList();
            echo "read all local_nodes from disk into list\n";
            echo "No versioning info added (no vcs specified)\n";
        }

        $prime_local_nodes = $this->getPrimeData($local_nodes, $this->prefix);
        echo "parsed all disk data into prime data\n";

        $database_nodes = $this->getDbNodes();
        echo "read all entries from database into list\n";
        $prime_database_nodes = $this->getPrimeData($database_nodes);
        echo "parsed all database data into prime data\n";

        // Find new and deleted local_nodes
        $new_local_nodes     = array_diff_key($prime_local_nodes, $prime_database_nodes);
        $deleted_local_nodes = array_diff_key($prime_database_nodes, $prime_local_nodes);

        // Remove the new and deleted nodes from the original sets
        $cleaned_database_nodes = array_intersect_key($prime_database_nodes, $prime_local_nodes);
        $cleaned_local_nodes    = array_intersect_key($prime_local_nodes, $prime_database_nodes);

        // Search for differences from local_nodes
        // data compared to the database
        $diff = array_diff_assoc($cleaned_local_nodes, $cleaned_database_nodes);

        $new_file_functions     = $this->getFileFunctionDifference($prime_local_nodes, $prime_database_nodes);
        $deleted_file_functions = $this->getFileFunctionDifference($prime_database_nodes, $prime_local_nodes);

        $this->insertNewFileFunctions($new_file_functions);
        $this->updateDeadFunctions($deleted_file_functions);
        // Commit all data to the database
        $this->getDb()->beginTransaction();
        $this->insertNewFiles($new_local_nodes);
        $this->updateDeadFiles($deleted_local_nodes);
        $this->updateChangedFiles($diff, $cleaned_database_nodes);
        $this->getDb()->commit();
    }

    private function updateChangedFiles(array $diff, array $db)
    {
        if (count($diff) <= 0) {
            return;
        }

        $table = $this->getTable();
        $sql   = "";
        $query =
            "UPDATE $table SET deleted_at = NULL, last_hit=last_hit, changed_at = %s" .
            "/* was: %s */ WHERE file = \"%s\";\n";
        foreach ($diff as $file => $data) {
            $changed_at         = $data->getSQLChangedAt();
            $current_changed_at = $db[$file]->getSQLChangedAt();
            $sql               .= sprintf($query, $changed_at, $current_changed_at, $file);
        }

        $sql;
        $this->getDb()->exec($sql);
    }

    private function insertNewFiles(array $new)
    {
        $table     = $this->getTable();
        $db        = $this->getDb();
        $new_files = array_chunk($new, 1000, true);
        foreach ($new_files as $new) {
            $values = "";
            foreach ($new as $file => $data) {
                /*
                 * @var $data PrimeData
                 */
                $safe_file  = $db->quote($file);
                $changed_at = $data->getSQLChangedAt();
                $values    .= "($safe_file,NOW(),$changed_at),\n";
            }
            $values = substr($values, 0, -2);

            $query =
                "INSERT INTO $table (file,added_at,changed_at) VALUES\n$values";
            $db->exec($query);
        }
    }

    private function updateDeadFiles($removed)
    {
        if (!count($removed)) {
            return;
        }

        $table  = $this->getTable();
        $values = implode("\",\"", array_keys($removed));
        $query  =
            "UPDATE $table SET deleted_at = NOW() WHERE deleted_at IS NULL AND file IN (\"$values\")";
        $this->getDb()->exec($query);
    }


    /**
     * Constructs the SQL query and executes it.
     * @param Node[] $new
     */
    private function insertNewFileFunctions(array $new)
    {
        // early return when there is nothing to be added
        if (empty($new)) {
            return;
        }
        $table = $this->getFunctionsTable();
        $db    = $this->getDb();

        foreach ($new as $file_function) {
            $query = $db->prepare("INSERT INTO $table (function, added_at) VALUES (:file_function, NOW())");
            $query->bindParam(":file_function", $file_function);
            $query->execute();
        }
    }

    private function updateDeadFunctions(array $dead_functions): void
    {
        if (empty($dead_functions)) {
            return;
        }
        $table = $this->getFunctionsTable();
        $db    = $this->getDb();

        foreach ($dead_functions as $file_function) {
            $query = $db->prepare(
                "UPDATE $table SET deleted_at = NOW() WHERE deleted_at IS NULL AND function = :file_function"
            );
            $query->bindParam(":file_function", $file_function);
            $query->execute();
        }
    }
}
