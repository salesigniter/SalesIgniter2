<?php
/*
  Package Products Version 1

  I.T. Web Experts, Rental Store v2
  http://www.itwebexperts.com

  Copyright (c) 2011 I.T. Web Experts

  This script and it's source is not redistributable
 */
class ProductsPackagedProducts extends Doctrine_Record {

	public function setUp(){
		parent::setUp();

		$this->setAttribute(Doctrine::ATTR_COLL_KEY, 'product_id');

		$this->hasOne('Products as ProductInfo', array(
			'local' => 'product_id',
			'foreign' => 'products_id'
		));
	}
	public function preSave($event)
	{
		if (is_array($this->package_data)){
			$this->package_data = json_encode($this->package_data);
		}
	}

	public function preHydrate($event)
	{
		$data = $event->data;
		if (isset($data['package_data'])){
			$data['package_data'] = json_decode($data['package_data'], true);
			$event->data = $data;
		}
	}


	public function setTableDefinition(){
		$this->setTableName('products_packaged_products');

		$this->hasColumn('package_id', 'integer', 4, array(
			'type' => 'integer',
			'length' => 4,
			'unsigned' => 0,
			'primary' => false,
			'autoincrement' => false,
		));

		$this->hasColumn('product_id', 'integer', 4, array(
			'type' => 'integer',
			'length' => 4,
			'unsigned' => 0,
			'primary' => false,
			'autoincrement' => false,
		));

		$this->hasColumn('package_data', 'string', 999, array(
			'type' => 'string',
			'length' => 999
		));
	}
}