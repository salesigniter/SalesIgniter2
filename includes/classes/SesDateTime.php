<?php
class SesDateTime extends DateTime
{

	public function format($format){
		if ($this->getTimestamp() <= 0){
			$return = 'Not Set';
		}else{
			$return = parent::format($format);
		}
		return $return;
	}

	public static function createFromFormat($format, $time, DateTimeZone $timezone = null){
		$Orig = parent::createFromFormat($format, $time);
		$return = new SesDateTime();
		if (!$Orig){
			$return->setTimestamp(0);
		}else{
			$return->setTimestamp($Orig->getTimestamp());
		}
		return $return;
	}

	//public function __toString(){
	//	return $this->format(DATE_TIMESTAMP);
	//}
}