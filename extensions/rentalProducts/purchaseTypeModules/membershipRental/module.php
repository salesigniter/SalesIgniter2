<?php
/*
	Product Purchase Type: Membership Rental

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2011 I.T. Web Experts

	This script and it's source is not redistributable
*/

/**
 * Rental Membership Purchase Type
 * @package ProductPurchaseTypes
 */

class PurchaseType_MembershipRental extends PurchaseTypeBase
{

	public function __construct($forceEnable = false) {
		$this->setTitle('Membership Rental');
		$this->setDescription('Membership Based Rentals Which Mimic Sites Like netflix.com');

		$this->init(
			'membershipRental',
			$forceEnable,
			sysConfig::getDirFsCatalog() . 'extensions/rentalProducts/purchaseTypeModules/membershipRental/'
		);
	}

	public function hasInventory() {
		if ($this->isEnabled() === false) {
			return false;
		}
		return true;
	}

	public function getPurchaseHtml($key) {
		global $rentalQueue, $userAccount;

		$return = null;
		switch($key){
			case 'product_info':
				$button = htmlBase::newElement('button')
					->setType('submit')
					->setText(sysLanguage::get('TEXT_BUTTON_IN_QUEUE'))
					->setName('add_queue');
				if (isset($this->productInfo['isBox']) && $this->productInfo['isBox'] === true) {
					$button->setText(sysLanguage::get('TEXT_BUTTON_IN_QUEUE_SERIES'))->setName('add_queue_all');
				}

				if ($this->existsInQueue() === true){
					switch($this->getConfigData('EXISTS_IN_QUEUE_PRODUCT_INFO_DISPLAY')){
						case 'disable':
							$button->disable();
							break;
						case 'in_queue_text':
							$button = htmlBase::newElement('span')
								->addClass('existsInQueue')
								->html(sysLanguage::get('TEXT_EXISTS_IN_QUEUE'));
							break;
						case 'Hide Box':
							return null;
							break;
					}
				}

				if ($userAccount->isLoggedIn() === false){
					$allowQty = false;
					$button = htmlBase::newElement('button')
						->setHref(itw_app_link(null, 'account', 'login'))
						->setText(sysLanguage::get('TEXT_BUTTON_LOGIN_REQUIRED'));
				}

				$content = '';
				if ($this->showRentalAvailability()){
					$content = '<table cellpadding="1" cellspacing="0" border="0"><tr>
						<td class="main">' . sysLanguage::get('TEXT_AVAILABLITY') . '</td>
						<td class="main">' . $this->getAvailabilityName() . '</td>
					   </tr></table>';
				}

				$return = array(
					'form_action' => itw_app_link(tep_get_all_get_params(array('action'))),
					'purchase_type' => $this->getCode(),
					'allowQty' => false,
					'header' => $this->getTitle(),
					'content' => $content,
					'button' => $button
				);
				break;
		}
		return $return;
	}

	function showRentalAvailability() {
		return ($this->getConfigData('PRODUCT_INFO_SHOW_AVAILABILITY') == 'True');
	}

	function getAvailabilityName() {

		$QproductsInQueue = Doctrine_Query::create()
			->from('RentalQueueTable')
			->where('products_id = ?', $this->productInfo['id'])
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

		$QAvailability = Doctrine_Query::create()
			->from('RentalAvailability r')
			->leftJoin('r.RentalAvailabilityDescription rd')
			->where('rd.language_id = ?', Session::get('languages_id'))
			->orderBy('r.ratio')
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

		$availability = count($QproductsInQueue) - $this->getCurrentStock();
		$availabilityName = null;

		if ($QAvailability){
			foreach($QAvailability as $aInfo){
				if ($availability <= $aInfo['ratio']){
					$availabilityName = $aInfo['RentalAvailabilityDescription'][0]['name'];
					break;
				}
			}
		}

		return $availabilityName;
	}

	public function showProductListing($col){
		global $rentalQueue;
		$return = false;
		if ($col == 'membershipRental'){
			if ($this->hasInventory()){
				$rentNowButton = htmlBase::newElement('button')
					->setText(sysLanguage::get('TEXT_BUTTON_IN_QUEUE'))
					->setHref(itw_app_link(tep_get_all_get_params(array('action')) . 'action=rent_now&products_id=' . $this->getProductId()), true);

				if ($rentalQueue->in_queue($this->getProductId()) === true){
					$rentNowButton->disable();
				}

				$return = $rentNowButton->draw();
			}
		}
		return $return;
	}

	function rentalAllowed(){
		global $userAccount;

		if ($userAccount->isRentalMember()){
			if ($userAccount->membershipIsActivated()){
				$membership =& $userAccount->plugins['membership'];
				if($membership->isPastDue()){
					return 'pastdue';
				}else{
					return true;
				}
			}else{
				return 'inactive';
			}
		}else{
			return 'membership';
		}
	}

	public function existsInQueue(){
		$RentalQueue =& Session::getReference('RentalQueue');
		return $RentalQueue->inQueue($this->getProductId());
	}

	public function addToQueuePrepare(array &$QueueProductData){

	}
}

?>