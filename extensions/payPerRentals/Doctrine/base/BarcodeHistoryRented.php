<?php
/*
Pay Per Rental Products Extension Version 1

I.T. Web Experts, Rental Store v2
http://www.itwebexperts.com

Copyright (c) 2009 I.T. Web Experts

This script and it's source is not redistributable
*/

class BarcodeHistoryRented extends Doctrine_Record {
	
	public function setUp(){
		$this->setUpParent();
		$this->hasOne('ProductsInventoryBarcodes', array(
				'local'   => 'barcode_id',
				'foreign' => 'barcode_id'
		));

	}

	public function setUpParent(){

		$ProductsInventoryBarcodes = Doctrine_Core::getTable('ProductsInventoryBarcodes')->getRecordInstance();

		$ProductsInventoryBarcodes->hasMany('BarcodeHistoryRented', array(
				'local'   => 'barcode_id',
				'foreign' => 'barcode_id'
			));
	}

	
	public function setTableDefinition(){
		$this->setTableName('barcode_history_rented');


        $this->hasColumn('barcode_id', 'integer', 4, array(
			'type' => 'integer',
			'length' => 4,
			'unsigned' => 0,
			'primary' => true,
			'notnull' => false,
			'autoincrement' => false,
		));

        $this->hasColumn('number_rents', 'integer', 4, array(
		        'type' => 'integer',
		        'length' => 4,
		        'unsigned' => 0,
		        'primary' => false,
		        'default' => 0,
		        'notnull' => false,
		        'autoincrement' => false,
	    ));
        $this->hasColumn('last_maintenance_date', 'datetime', null, array(
		        'type'          => 'datetime',
		        'primary'       => false,
		        'notnull'       => true,
		        'autoincrement' => false
	    ));
        $this->hasColumn('last_biweekly_date', 'datetime', null, array(
		        'type'          => 'datetime',
		        'primary'       => false,
		        'notnull'       => true,
		        'autoincrement' => false
	    ));
        $this->hasColumn('last_monthly_date', 'datetime', null, array(
		        'type'          => 'datetime',
		        'primary'       => false,
		        'notnull'       => true,
		        'autoincrement' => false
	    ));
        $this->hasColumn('last_quarantine_date', 'datetime', null, array(
		        'type'          => 'datetime',
		        'primary'       => false,
		        'notnull'       => true,
		        'autoincrement' => false
	    ));

	}
}