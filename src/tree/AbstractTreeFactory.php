<?php

abstract class AbstractTreeFactory implements ITreeFactory{
	/**
	 *
	 * @return Tree
	 */
	public function &produceTree() {
		$leaves = $this->produceList();
		$root = new Tree ( "/" ); // Create the root node of the file tree
	
		foreach ( $leaves as $key => $node ) {
			/* @var $node Node */
			$pointer = &$root; // Create a reference for tree walking
			$path = explode ( DIRECTORY_SEPARATOR, trim ( $node->getLocation(), DIRECTORY_SEPARATOR ) );
			array_pop($path);
				
			foreach ( $path as $part ) {
				$pointer = &$pointer->addChildByRelativePath ( $part );
			}
				
			$pointer->addChild($leaves[$key]);
		}
		return $root;
	}
}

?>