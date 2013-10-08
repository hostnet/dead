<?php

class Tree extends Node {
	public function __toString() {
		return $this->toStringRecursive();
	}
}

?>