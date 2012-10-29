<?php
require_once "AbstractVersioningVisitor.php";
require_once "common/Version.php";

class GitVisitor extends AbstractVersioningVisitor {
	private $gitCommand = "/usr/bin/env git";
	
	private function execGitVersion() {
		$result = `$this->gitCommand --version`;
		$result = str_replace ( "git version ", "", $result );
		return $result;
	}
	
	private function execGit($path, $max) {
		$command = sprintf ( "%s log -n %d -- %s 2>&1", $this->gitCommand, $max, $path );
		$result = `$command`;
		return $result;
	}

	private function processExecGitResult($result) {
		$commits = array();
		preg_match_all("/commit ([a-f0-9]+)\nAuthor: ([^\n]+)\nDate:   ([^\n]+)\n\n(.*)(?:\n$|\ncommit)/msU",$result , $matches, PREG_SET_ORDER);
		
		foreach($matches as $match) {
			$id = $match[1];
			$author = $match[2];
			//Sat Jan 21 20:43:18 2012 +0100
			$date = DateTime::createFromFormat("D M j G:i:s Y O",$match[3]);
			$message = $match[4];
			$commit = new Commit($id,$author,$date,$message);
			$commits[] = $commit;
		}
		
		return $commits;
	}
	
	protected function getCommits($path, $max) {
		$v = $this->execGitVersion ();
		 
	  if ( Version::compare($v, "1.7.0.4") >= Version::EQUAL) {
			$result = $this->execGit ( $path, $max );
			return $this->processExecGitResult($result);
		}
	}
}

?>
