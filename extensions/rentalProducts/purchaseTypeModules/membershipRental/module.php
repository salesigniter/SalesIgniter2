<?php
/*
	Product Purchase Type: Rental

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

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
				$button = htmlBase::newElement('button')->setType('submit');
					//print_r($this->getProduct);
					$Product = new Product($this->productInfo['id']);
				if ($Product->isBox() === false){
					$button->setText(sysLanguage::get('TEXT_BUTTON_IN_QUEUE'))->setName('add_queue');
					if ($rentalQueue->in_queue($this->productInfo['id']) === true){
						$button->disable();
					}
				}
				elseif ($Product->isBox() === true) {
					$button->setText(sysLanguage::get('TEXT_BUTTON_IN_QUEUE_SERIES'))->setName('add_queue_all');
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
					'allowQty' => ($this->getConfigData('ALLOW_MULTIPLE_IN_CART') == 'True' && $this->getConfigData('ALLOWED_PRODUCT_INFO_QUANTITY_FIELD') == 'True'),
					'header' => $this->getTitle(),
					'content' => $content,
					'button' => $button
				);
				break;
		}
		return $return;
	}

	/**
	 * @param array $CartProductData
	 * @return bool
	 */
	public function allowAddToCart($CartProductData){
		global $messageStack, $userAccount;
		return true;
	}

	public function addToCartPrepare(&$pInfo){
		$ProdClass = new Product($this->getProductId());
		$pInfo['price'] = $ProdClass->getKeepPrice();
		$pInfo['final_price'] = $ProdClass->getKeepPrice();
		$pInfo['queue_id'] = $_POST['queue_id'];
	}

	public function processAddToCart(&$pInfo) {
		EventManager::notify('PurchaseTypeAddToCart', $this->getCode(), &$pInfo, $this->productInfo);
	}

	public function updateStock($orderId, $orderProductId, &$cartProduct) {
		return false;
	}

	public function onInsertOrderedProduct($cartProduct, $orderId, &$orderedProduct, &$products_ordered) {
		$pID = (int)$cartProduct->getIdString();
		$pInfo = $cartProduct->getInfo();
		$queueID = $pInfo['queue_id'];
		$InventoryCls =& $this->getInventoryClass();

		$Qrented = Doctrine_Query::create()
		->from('RentedQueue')
		->where('customers_queue_id = ?', $queueID)
		->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

		$Barcode = Doctrine_Core::getTable('ProductsInventoryBarcodes')->find($Qrented[0]['products_barcode']);
		if ($Barcode){
			$Barcode->status = 'P';
			$Barcode->save();
		}

		Doctrine_Query::create()
		->delete('RentedQueue')
		->where('customers_queue_id = ?', $queueID)
		->execute();
		$orderedProduct->purchase_type = $this->getCode();
		$orderedProduct->save();

	}


	function showRentalAvailability() {
		if (sysConfig::get('ALLOW_RENTALS') == 'false' || sysConfig::get('RENTAL_AVAILABILITY_PRODUCT_INFO') == 'false'){
			return false;
		}
		return (sysConfig::get('RENTAL_AVAILABILITY_PRODUCT_INFO') == 'true');
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
}

?>