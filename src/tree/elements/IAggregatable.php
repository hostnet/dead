<?php

interface IAggregatable {
	public function aggregate($object);
	public function getAggregateKey();
}

?>