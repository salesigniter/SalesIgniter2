<?php
/**
 * Info class for the order class
 *
 * @package   Order\InfoManager
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @since     2.0
 * @copyright 2012 I.T. Web Experts
 * @license   http://itwebexperts.com/license/ses-license.php
 */

class OrderInfo
{

	/**
	 * @var string
	 */
	protected $key = '';

	/**
	 * @var string
	 */
	protected $val = '';

	/**
	 * @param string $k
	 * @param string $v
	 */
	public function __construct($k = '', $v = '')
	{
		$this->key = $k;
		$this->val = $v;
		if (($k == 'date_added' || $k == 'last_modified') && is_array($v)){
			$Date = $this->val['date'];
			$TimeZone = $this->val['timezone'];
			$this->val = SesDateTime::createFromFormat(DATE_TIMESTAMP, $this->val['date']);
			$this->val->setTimezone(new DateTimeZone($TimeZone));
		}
	}

	/**
	 * @return string
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * @param $val
	 */
	public function setKey($val)
	{
		$this->key = $val;
	}

	/**
	 * @return DateTime|SesDateTime|string
	 */
	public function getValue()
	{
		return $this->val;
	}

	/**
	 * @param $val
	 */
	public function setValue($val)
	{
		$this->val = $val;
	}

	/**
	 * @return array
	 */
	public function prepareJsonSave()
	{
		return array(
			'key'   => $this->getKey(),
			'value' => $this->getValue()
		);
	}
}

?>