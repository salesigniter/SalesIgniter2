<?php
class FileWriterXlsCol
{

	private $value;

	public function __construct($colText) {
		$this->value = $colText;
	}

	function getValue() {
		if ($this->value instanceof DateTime || $this->value instanceof SesDateTime){
			return $this->value->format(sysLanguage::getDateTimeFormat());
		}else{
			return $this->value;
		}
	}
}