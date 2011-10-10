<?php
/*
	Product Purchase Type: Member Stream

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

/**
 * Rental Membership Stream Purchase Type
 * @package ProductPurchaseTypes
 */
class PurchaseType_MembershipStream extends PurchaseTypeBase
{

	public function __construct($forceEnable = false) {
		$this->setTitle('Membership Stream');
		$this->setDescription('Membership Based Stream Products Which Mimic Sites Like netflix.com');

		$this->init(
			'membershipStream',
			$forceEnable,
			sysConfig::getDirFsCatalog() . 'extensions/streamProducts/purchaseTypeModules/membershipStream/'
		);
	}

	public function hasInventory() {
		return true;
	}

	public function updateStock($orderId, $orderProductId, &$cartProduct) {
		return false;
	}

	public function getPurchaseHtml($key) {
		global $userAccount;
		$return = null;
		switch($key){
			case 'product_info':
				$button = htmlBase::newElement('button')
					->setType('submit')
					->setName('stream_product')
					->setText(sysLanguage::get('TEXT_BUTTON_VIEW_STREAM'));

				$allowQty = ($this->getConfigData('ALLOW_MULTIPLE_IN_CART') == 'True' && $this->getConfigData('ALLOWED_PRODUCT_INFO_QUANTITY_FIELD') == 'True');
				if ($this->hasInventory() === false){
					$allowQty = false;
					switch($this->getConfigData('OUT_OF_STOCK_PRODUCT_INFO_DISPLAY')){
						case 'Disable Button':
							$button->disable();
							break;
						case 'Out Of Stock Text':
							$button = htmlBase::newElement('span')
								->addClass('outOfStockText')
								->html(sysLanguage::get('TEXT_OUT_OF_STOCK'));
							break;
						case 'Hide Box':
							return null;
							break;
					}
				}
				
				if ($this->getConfigData('LOGIN_REQUIRED') == 'True'){
					if ($userAccount->isLoggedIn() === false){
						$allowQty = false;
						$button = htmlBase::newElement('button')
							->setHref(itw_app_link(null, 'account', 'login'))
							->setText(sysLanguage::get('TEXT_LOGIN_REQUIRED'));
					}
				}

				$return = array(
					'form_action' => itw_app_link(tep_get_all_get_params(array('action'))),
					'purchase_type' => $this->getCode(),
					'allowQty' => $allowQty,
					'header' => $this->getTitle(),
					'content' => '',
					'button' => $button
				);
				break;
		}
		return $return;
	}
}

?>