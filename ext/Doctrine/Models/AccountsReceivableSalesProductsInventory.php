<?php

class AccountsReceivableSalesProductsInventory extends Doctrine_Record
{

	public function setUp()
	{
		$this->hasOne('ProductsInventoryItems as InventoryItem', array(
			'local'   => 'item_id',
			'foreign' => 'item_id'
		));
	}

	public function setTableDefinition()
	{
		$this->setTableName('accounts_receivable_sales_products_inventory');

		$this->hasColumn('sale_product_id', 'integer', 4);
		$this->hasColumn('serial_id', 'integer', 4);
	}
}