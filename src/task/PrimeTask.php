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
        $factory->query(PdoTreeFactory::FUNCTIONS_ALL);
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

        // Commit all data to the database
        $this->getDb()->beginTransaction();
        $this->insertNewFileFunctions($new_local_nodes);
        $this->updateDeadFunctions($deleted_local_nodes);
        $this->getDb()->commit();
    }

    /**
     * Constructs the SQL query and executes it.
     * @param iterable|string[] $new
     */
    private function insertNewFileFunctions(iterable $new): void
    {
        // early return when there is nothing to be added
        if (0 === \count($new)) {
            return;
        }
        $table = $this->getFunctionsTable();
        $db    = $this->getDb();

        foreach ($new as $file_function => $data) {
            $query = $db->prepare("INSERT INTO $table (function, added_at) VALUES (:file_function, NOW())");
            $query->bindParam(":file_function", $file_function);
            $query->execute();
        }
    }


    /**
     * @param iterable|string[] $dead_functions
     */
    private function updateDeadFunctions(iterable $dead_functions): void
    {
        if (0 === \count($dead_functions)) {
            return;
        }
        $table = $this->getFunctionsTable();
        $db    = $this->getDb();

        foreach ($dead_functions as $file_function => $data) {
            $query = $db->prepare(
                "UPDATE $table SET deleted_at = NOW() WHERE deleted_at IS NULL AND function = :file_function"
            );
            $query->bindParam(":file_function", $file_function);
            $query->execute();
        }
    }
}
