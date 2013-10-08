<?php

class FileTreeFactory extends AbstractTreeFactory {
	private $files = array ();
	
	/**
	 *
	 * @param $path string       	
	 * @param $extension string       	
	 * @return void
	 */
	public function scan($path, $extension = 'php') {
		try {
			foreach ( new RecursiveIteratorIterator ( new RecursiveDirectoryIterator ( $path ) ) as $file ) {
				/*
				 * @var $file SplFileInfo
				 */
				if (pathinfo($file, PATHINFO_EXTENSION) == 'php') {
					$this->addFile($file->getPathname());
				}
			}
		} catch ( UnexpectedValueException $e ) {
			echo "Could not open dir $path" . PHP_EOL;
		} catch ( Exception $e ) {
			die ( $e->getMessage () );
		}
	
	}
	
	/**
	 *
	 * @param $filename string       	
	 */
	public function addFile($filename) {
			$this->files [] = new Node($filename);
	}
	

	public function &produceList() {
		return $this->files;
	}

}
