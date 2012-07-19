<?php

class AccountsReceivableSalesProductsPackaged extends Doctrine_Record {

	public function setUp(){
		$this->hasMany('AccountsReceivableSalesProductsInventory as Inventory', array(
			'local' => 'sale_product_id',
			'foreign' => 'sale_product_id',
			'cascade' => array('delete')
		));

		$this->hasOne('AccountsReceivableSalesProducts as Parent', array(
			'local' => 'package_id',
			'foreign' => 'id'
		));
	}

	public function preSave($event)
	{
		if (is_array($this->product_json)){
			$this->product_json = json_encode($this->product_json);
		}
	}

	public function preHydrate($event)
	{
		$data = $event->data;
		if (isset($data['product_json'])){
			$data['product_json'] = json_decode($data['product_json'], true);
			$event->data = $data;
		}
	}

	public function setTableDefinition(){
		$this->setTableName('accounts_receivable_sales_products_packaged');

		$this->hasColumn('package_id', 'integer', 4);
		$this->hasColumn('sale_product_id', 'integer', 4);

		$this->hasColumn('product_json', 'string', 999);
	}
}