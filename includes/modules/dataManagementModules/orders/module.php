<?php
class DataManagementModuleOrders extends DataManagementModuleBase
{

	public function __construct() {
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Order Data Management');
		$this->setDescription('Import And Export Orders Using This Module');

		$this->init(
			'orders',
			true,
			__DIR__
		);
	}

	public function getSupportedColumns() {
		$supportedColumns = array(
			'v_orders_id' => true,
			'v_orders_customers_name' => true,
			'v_orders_customers_company' => true,
			'v_orders_customers_email_address' => true,
			'v_orders_customers_telephone' => true,
			'v_orders_billing_name' => true,
			'v_orders_billing_address' => true,
			'v_orders_billing_city' => true,
			'v_orders_billing_state' => true,
			'v_orders_billing_country' => true,
			'v_orders_billing_postcode' => true,
			'v_orders_shipping_name' => true,
			'v_orders_shipping_address' => true,
			'v_orders_shipping_city' => true,
			'v_orders_shipping_state' => true,
			'v_orders_shipping_country' => true,
			'v_orders_shipping_postcode' => true,
			'v_orders_subtotal' => true,
			'v_orders_total' => true,
			'v_orders_tax' => true,
			'v_orders_payment_method' => true,
			'v_orders_status' => true,
			'v_orders_shipping_price' => true,
			'v_orders_shipping_method' => true,
			'v_orders_date_purchased' => true,
			'v_orders_products_name' => true,
			'v_orders_products_model' => true,
			'v_orders_products_price' => true,
			'v_orders_products_tax' => true,
			'v_orders_products_finalprice' => true,
			'v_orders_products_qty' => true,
			'v_orders_products_barcode' => true,
			'v_orders_products_purchasetype' => true
		);

		EventManager::notify('AdminOrdersListingExportFields', &$supportedColumns);

		return $supportedColumns;
	}

	public function runImport(){

	}

	public function runExport(){

	}
}
