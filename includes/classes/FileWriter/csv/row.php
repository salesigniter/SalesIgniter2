<?php
class FileWriterCsvRow
{

	private $columns = array();

	private $colRefs = array();

	public function __construct($colRefs = null) {
		if (is_null($colRefs) === false){
			$this->colRefs = $colRefs;
		}
	}

	public function hasColumn($key){
		return ($this->getColumn($key) !== false);
	}

	public function removeColumn($key){
		if (isset($this->columns[$key]) === true){
			unset($this->columns[$key]);
		}
	}

	public function addColumn($colValue, $colKey = null){
		$NewColumn = new FileWriterCsvCol($colValue);

		if (is_null($colKey) === false){
			$this->columns[$colKey] =& $NewColumn;
		}else{
			$this->columns[] =& $NewColumn;
		}

		return $NewColumn;
	}

	public function getColumn($key){
		if (is_numeric($key) && isset($this->columns[$key])){
			return $this->columns[$key];
		}elseif (is_string($key)){
			foreach($this->columns as $k => $Col){
				if ($Col->getValue() === $key || $k === $key){
					return $Col;
				}
			}
		}
		return false;
	}

	public function getColumns(){
		return $this->columns;
	}

	public function getRefArray(){
		$refArray = array();
		foreach($this->columns as $k => $Col){
			$refArray[$Col->getValue()] = $k;
		}
		return $refArray;
	}
}