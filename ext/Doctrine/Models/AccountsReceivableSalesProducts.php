<?php

class AccountsReceivableSalesProducts extends Doctrine_Record {

	public function setUp(){
		$this->hasOne('AccountsReceivableSales as Sale', array(
			'local' => 'sale_id',
			'foreign' => 'id'
		));

		$this->hasMany('AccountsReceivableSalesProductsInventory as SaleInventory', array(
			'local' => 'id',
			'foreign' => 'sale_product_id',
			'cascade' => array('delete')
		));
	}

	public function setTableDefinition(){
		$this->setTableName('accounts_receivable_sales_products');

		$this->hasColumn('sale_id', 'integer', 4);

		$this->hasColumn('product_json', 'string', 999);
	}
}