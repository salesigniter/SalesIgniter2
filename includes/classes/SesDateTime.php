<?php
class SesDateTime extends DateTime
{

	const TIME_MINUTE = 60;

	const TIME_MINUTE_MINUTES = 1;

	const TIME_HOUR = 3600;

	const TIME_HOUR_MINUTES = 60;

	const TIME_DAY = 86400;

	const TIME_DAY_MINUTES = 1440;

	const TIME_WEEK = 604800;

	const TIME_WEEK_MINUTES = 10080;

	public function format($format)
	{
		if ($this->getTimestamp() <= 0){
			$return = '';
		}
		else {
			$return = parent::format($format);
		}
		return $return;
	}

	/**
	 * @static
	 * @param string            $format
	 * @param string            $time
	 * @param DateTimeZone|null $timezone
	 * @return DateTime|SesDateTime
	 */
	public static function createFromFormat($format, $time, DateTimeZone $timezone = null)
	{
		$Orig = parent::createFromFormat($format, $time);
		$return = new SesDateTime();
		if (!$Orig){
			$return->setTimestamp(0);
		}
		else {
			$return->setTimestamp($Orig->getTimestamp());
		}
		return $return;
	}

	/**
	 * @static
	 * @param $array
	 * @return DateTime|SesDateTime
	 */
	public static function createFromArray($array)
	{
		if ($array instanceof SesDateTime){
			$Date = $array;
		}else{
			$Date = SesDateTime::createFromFormat(DATE_TIMESTAMP, $array['date']);
			$Date->setTimezone(new DateTimeZone($array['timezone']));
		}
		return $Date;
	}

	//public function __toString(){
	//	return $this->format(DATE_TIMESTAMP);
	//}
}