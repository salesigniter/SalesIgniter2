<?php
class SesJson
{
	private $_data = array();

	public function __construct($data = ''){
		if (is_array($data)){
			$dataString = json_encode($data);
			$decoded = json_decode($dataString, true);
		}
		elseif (!empty($data)){
			$decoded = json_decode($data, true);
		}

		if (isset($decoded)){
			$this->iterateArray($decoded, $this);
		}
	}

	private function iterateArray($arr, &$mainVar){
		foreach($arr as $k => $v){
			if (is_array($v)){
				$mainVar->$k = SesJson::decode($v);
			}else{
				$mainVar->$k = $v;
			}
		}
	}

	public static function encode($data){
		return json_encode($data);
	}

	public static function decode($data){
		return new SesJson($data);
	}

	public function __get($k){
		if (isset($this->_data[$k])){
			return $this->_data[$k];
		}
		return null;
	}

	public function __set($k, $v){
		$this->_data[$k] = $v;
	}

	public function __isset($k){
		return isset($this->_data[$k]);
	}

	public function __toString()
	{
		return json_encode($this->_data);
	}
}