<?php

class AccountsReceivableSalesProducts extends Doctrine_Record
{

	public function setUp()
	{
		$this->hasOne('Products as Product', array(
			'local'   => 'product_id',
			'foreign' => 'products_id'
		));

		$this->hasOne('AccountsReceivableSales as Sale', array(
			'local'   => 'sale_id',
			'foreign' => 'id'
		));

		$this->hasMany('AccountsReceivableSalesProductsInventory as SaleInventory', array(
			'local'   => 'id',
			'foreign' => 'sale_product_id'
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

	public function setTableDefinition()
	{
		$this->setTableName('accounts_receivable_sales_products');

		$this->hasColumn('sale_id', 'integer', 4);
		$this->hasColumn('product_id', 'integer', 4);
		$this->hasColumn('products_model', 'string', 255);
		$this->hasColumn('products_name', 'string', 255);
		$this->hasColumn('products_price', 'decimal', 15, array(
			'scale' => 4
		));
		$this->hasColumn('products_tax', 'decimal', 15, array(
			'scale' => 4
		));
		$this->hasColumn('products_tax_class_id', 'integer', 4);
		$this->hasColumn('products_quantity', 'integer', 2);
		$this->hasColumn('products_weight', 'decimal', 15, array(
			'scale' => 4
		));
		$this->hasColumn('products_type', 'string', 32);

		$this->hasColumn('product_json', 'string', 999);
	}
}