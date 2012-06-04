<?php

class AccountsReceivableSalesProductsInventory extends Doctrine_Record {

	public function setUp(){
		$this->hasOne('ProductsInventoryBarcodes as Barcode', array(
			'local' => 'barcode_id',
			'foreign' => 'barcode_id'
		));
		$this->hasOne('ProductsInventoryQuantity as Quantity', array(
			'local' => 'quantity_id',
			'foreign' => 'quantity_id'
		));
	}

	public function setTableDefinition(){
		$this->setTableName('accounts_receivable_sales_products_inventory');

		$this->hasColumn('sale_product_id', 'integer', 4);
		$this->hasColumn('barcode_id', 'integer', 4);
		$this->hasColumn('quantity_id', 'integer', 4);
	}
}