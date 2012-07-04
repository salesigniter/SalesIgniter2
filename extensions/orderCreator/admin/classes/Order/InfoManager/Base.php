<?php
/**
 * Info manager for the order class
 *
 * @package    OrderCreator\InfoManager
 * @author     Stephen Walker <stephen@itwebexperts.com>
 * @since      2.0
 * @copyright  2012 I.T. Web Experts
 * @license    http://itwebexperts.com/license/ses-license.php
 */

class OrderCreatorInfoManager extends OrderInfoManager
{

	/**
	 * @return OrderCreatorInfo|OrderInfo
	 */
	public function getInfoObjectClass()
	{
		return new OrderCreatorInfo();
	}
}

require(__DIR__ . '/Info.php');
