<?php
/*
	Multi Stores Extension Version 1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class ProductsInventoryBarcodesTransfers extends Doctrine_Record
{

	public function setUp() {
		parent::setUp();

		$this->hasOne('ProductsInventoryBarcodes', array(
				'local' => 'barcode',
				'foreign' => 'barcode'
			));

		$ProductsInventoryBarcodes = Doctrine_Core::getTable('ProductsInventoryBarcodes')->getRecordInstance();
		$ProductsInventoryBarcodes->hasMany('ProductsInventoryBarcodesTransfers', array(
				'local' => 'barcode',
				'orderBy' => 'transfer_id DESC',
				'foreign' => 'barcode',
				'cascade' => array('delete')
			));
	}

	public function setTableDefinition() {
		$this->setTableName('products_inventory_barcodes_transfers');

		$this->hasColumn('transfer_id', 'integer', 4, array(
				'type' => 'integer',
				'length' => 4,
				'unsigned' => 0,
				'primary' => true,
				'notnull' => true,
				'autoincrement' => true,
			));

		$this->hasColumn('barcode', 'string', 64, array(
				'type' => 'string',
				'length' => 64
			));

		$this->hasColumn('status', 'string', 1, array(
				'type' => 'string',
				'length' => 1
			));

		$this->hasColumn('date_added', 'string', 64, array(
				'type' => 'string',
				'length' => 64
			));

		$this->hasColumn('tracking_number', 'string', 128, array(
				'type' => 'string',
				'length' => 128
			));

		$this->hasColumn('origin_id', 'integer', 4, array(
				'type' => 'integer',
				'length' => 4,
				'unsigned' => 0,
				'primary' => false,
				'notnull' => true,
				'autoincrement' => false,
			));

		$this->hasColumn('destination_id', 'integer', 4, array(
				'type' => 'integer',
				'length' => 4,
				'unsigned' => 0,
				'primary' => false,
				'notnull' => true,
				'autoincrement' => false,
			));

		$this->hasColumn('is_history', 'integer', 1, array(
				'type' => 'integer',
				'length' => 1,
				'default' => '0'
			));
	}
}