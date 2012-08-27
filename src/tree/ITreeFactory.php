<?php

interface ITreeFactory {
	
	/**
	 * @return array[int]Node
	 */
	public function &produceList();
	
	/**
	 * @return Tree
	 */
	public function &produceTree();
}

?>