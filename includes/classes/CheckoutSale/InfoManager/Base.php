<?php
/**
 * Info manager for the checkout sale
 *
 * @package   CheckoutSale
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

require(dirname(__FILE__) . '/Info.php');

/**
 * @package CheckoutSale
 */
class CheckoutSaleInfoManager extends OrderInfoManager
{

	/**
	 * @var bool
	 */
	protected $_hasError = false;

	/**
	 * @param array|null $infoArray
	 */
	public function __construct(array $infoArray = null)
	{
		if (!empty($infoArray)){
			foreach($infoArray as $k => $v){
				$this->info[$k] = new CheckoutSaleInfo($k, $v);
			}
		}
	}

	public function setInfo($k, $v)
	{
		$this->info[$k] = new CheckoutSaleInfo($k, $v);
	}

	public function hasError($val = null)
	{
		if ($val !== null){
			$this->_hasError = $val;
		}
		return $this->_hasError;
	}

	public function validate()
	{
		global $messageStack, $userAccount;
		$CustomerCheck = Doctrine_Core::getTable('Customers');
		$DbValidation = $CustomerCheck->validateField('customers_email_address', $this->getInfo('customers_email_address'));
		if ($DbValidation->count() > 0){
			$this->hasError(true);

			$FieldError = $DbValidation->get('customers_email_address');
			foreach($FieldError as $ErrorType){
				if ($ErrorType == 'notblank'){
					$messageStack->addSession('pageStack', 'You must enter an email address', 'error');
				}
				elseif ($ErrorType == 'unique'){
					if ($userAccount->isLoggedIn() === true){
						$this->hasError(false);
					}
					else {
						$messageStack->addSession('pageStack', 'Your email address already exists, please log in or use another email address', 'error');
					}
				}
				elseif ($ErrorType == 'email') {
					$messageStack->addSession('pageStack', 'Your email address doesn\'t appear to be valid, please use another email address', 'error');
				}
				else {
					$messageStack->addSession('pageStack', 'An unknown error occured related to your email address (' . $ErrorType . ')', 'error');
				}
				break;
			}
		}
		return ($this->hasError() === false);
	}

	public function jsonDecode($data)
	{
		$infoArray = json_decode($data, true);
		if ($infoArray){
			foreach($infoArray as $k => $info){
				$this->info[$k] = new CheckoutSaleInfo(
					$info['key'],
					$info['value']
				);
			}
		}
	}
}
