<?php

class AccountsReceivableSalesProductsInventory extends Doctrine_Record
{

	public function setUp()
	{
		$this->hasOne('ProductsSerialNumbers as Serial', array(
			'local'   => 'serial_number',
			'foreign' => 'serial_number'
		));
	}

	public function setTableDefinition()
	{
		$this->setTableName('accounts_receivable_sales_products_inventory');

		$this->hasColumn('sale_product_id', 'integer', 4);
		$this->hasColumn('serial_number', 'string', 255);
	}
}