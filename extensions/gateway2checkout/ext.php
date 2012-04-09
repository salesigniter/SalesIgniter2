<?php
/*
  Gateway 2Checkout Version 1

  I.T. Web Experts, Rental Store v2
  http://www.itwebexperts.com

  Copyright (c) 2011 I.T. Web Experts

  This script and it's source is not redistributable
 */
class Extension_gateway2checkout extends ExtensionBase
{

	public function __construct() {
		parent::__construct('gateway2checkout');
	}

	public function init() {
		if ($this->isEnabled() === false){
			return;
		}

		EventManager::attachEvents(array(
			'CheckoutBeforeExecute'
		), null, $this);
	}

	/**
	 *
	 * Return Parameters Explained
	 *
	 * card_holder_name
	 *     Provides the customer's name.
	 * cart_id
	 *     The cart ID your cart assigned to the order.
	 * cart_order_id
	 *     A unique order ID from your program that is normally used to identify an incomplete sale in your
	 *     database so that it can be marked as paid.
	 * city
	 *     Provides the customer’s city.
	 * country
	 *     Provides the customer’s country.
	 * credit_card_processed
	 *     This parameter will always be passed back as Y.
	 * demo
	 *     Defines if an order was live, or if the order was a demo order. If the order was a demo, the MD5
	 *     hash will fail.
	 * email
	 *     Provides the email address the customer provided when placing the order.
	 * fixed
	 *     This parameter will only be passed back if it was passed into the purchase routine.
	 * ip_country
	 *     Provides the customer's IP location. Useful if you perform your own additional fraud review.
	 * key
	 *     An MD5 hash used to confirm the validity of a sale. It is calculated based on a combination of
	 *     your secret word, seller identification number, the order number, and the sale total.
	 * lang
	 *     Advises the language the customer was able to view the Order Details page in. Can be used to
	 *     track what language your customers speak or read. Can be helpful to track which pages or
	 *     purchase buttons customers are using if you have multiple languages on your site.
	 * merchant_order_id
	 *     The order ID you assigned to the order. This parameter will only be passed back if it was passed
	 *     into the purchase routine.
	 * order_number
	 *     The 2Checkout order number associated with the order.
	 * pay_method
	 *     Provides seller with the customer’s payment method. CC for Credit Card, PPI for PayPal.
	 * phone
	 *     Provides the phone number the customer provided when placing the order.
	 * ship_name
	 *     Provides the ship to name for the order.
	 * ship_street_address
	 *     Provides ship to address.
	 * ship_street_address2
	 *     Provides more detailed shipping address if more information is provided by the customer.
	 * ship_city
	 *     Provides ship to city.
	 * ship_state
	 *     Provides ship to state.
	 * ship_zip
	 *     Provides ship to zip or postal code.
	 * ship_country
	 *     Provides ship to country.
	 * sid
	 *     The seller identification number. This can be useful if your return script might be in use for
	 *     multiple 2Checkout accounts. The sid parameter is also used to form the returned MD5 hash
	 *     key to confirm the validity of a sale.
	 * state
	 *     Provides the state the customer gave when placing the order. Can be used for a customer data
	 *     base.
	 * street_address
	 *     Provides the customer’s street address.
	 * street_address2
	 *     Provides more detailed address if more information is provided by the customer.
	 * total
	 *     The amount the customer was billed on the order.
	 * zip
	 *     Provides the customer’s zip or postal code.
	 */
	public function CheckoutBeforeExecute() {
		global $App;
		if ($App->getPageName() == 'process2Checkout'){
			$Module = OrderPaymentModules::getModule('gateway2checkout');
			$passbackHash = strtoupper(md5(
				$Module->getConfigData('INS_SECRET') .
					$Module->getConfigData('VENDOR_ID') .
					$_GET['order_number'] .
					$_GET['total']
			));
			if ($passbackHash == $_GET['key']){
				$returnedParams = array(
					'card_holder_name'      => $_GET['card_holder_name'],
					'cart_id'               => $_GET['cart_id'],
					'cart_order_id'         => $_GET['cart_order_id'],
					'city'                  => $_GET['city'],
					'country'               => $_GET['country'],
					'credit_card_processed' => $_GET['credit_card_processed'],
					'demo'                  => $_GET['demo'],
					'email'                 => $_GET['email'],
					'fixed'                 => $_GET['fixed'],
					'ip_country'            => $_GET['ip_country'],
					'key'                   => $_GET['key'],
					'lang'                  => $_GET['lang'],
					'merchant_order_id'     => $_GET['merchant_order_id'],
					'order_number'          => $_GET['order_number'],
					'pay_method'            => $_GET['pay_method'],
					'phone'                 => $_GET['phone'],
					'ship_name'             => $_GET['ship_name'],
					'ship_street_address'   => $_GET['ship_street_address'],
					'ship_street_address2'  => $_GET['ship_street_address2'],
					'ship_city'             => $_GET['ship_city'],
					'ship_state'            => $_GET['ship_state'],
					'ship_zip'              => $_GET['ship_zip'],
					'ship_country'          => $_GET['ship_country'],
					'sid'                   => $_GET['sid'],
					'state'                 => $_GET['state'],
					'street_address'        => $_GET['street_address'],
					'street_address2'       => $_GET['street_address2'],
					'total'                 => $_GET['total'],
					'zip'                   => $_GET['zip']
				);
			}
		}
	}
}

?>