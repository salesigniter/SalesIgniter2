<?php
class FileParserCsvRow implements Iterator
{

	private $position = 0;

	private $line = array();

	private $colAssociations = array();

	public function __construct($lineArr, $colAssociations = array()) {
		$this->line = $lineArr;
		$this->position = 0;
		$this->colAssociations = $colAssociations;
	}

	private function getAssociation($position){
		$return = $position;
		if (isset($this->colAssociations[$position])){
			$return = $this->colAssociations[$position];
		}
		return $return;
	}

	public function getColumnValue($k, $ifEmpty = ''){
		$position = $k;
		if (in_array($k, $this->colAssociations) === true){
			$positionKeys = array_keys($this->colAssociations, $k);
			$position = $positionKeys[0];
		}
		//echo '<pre>';echo 'KEY::' . $k . '<br>POSITION::' . $position . '<br>';print_r($positionKeys);print_r($this->colAssociations);itwExit();

		if (isset($this->line[$position])){
			if (strlen($this->line[$position]) > 0){
				return $this->line[$position];
			}elseif ($ifEmpty !== ''){
				return $ifEmpty;
			}
			return null;
		}
		return false;
	}

	function rewind() {
		$this->position = 0;
	}

	function current() {
		return new FileParserCsvCol($this->line[$this->position], $this->getAssociation($this->position));
	}

	function key() {
		return $this->position;
	}

	function next() {
		++$this->position;
	}

	function valid() {
		return isset($this->line[$this->position]);
	}
}