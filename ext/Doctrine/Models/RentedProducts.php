<?php

/**
 * RentedProducts
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6401 2009-09-24 16:12:04Z guilhermeblanco $
 */
class RentedProducts extends Doctrine_Record {
	
	public function setUp(){
		$this->setUpParent();
	}
	
	public function setUpParent(){
		$Customers = Doctrine_Core::getTable('Customers')->getRecordInstance();
		$Products = Doctrine_Core::getTable('Products')->getRecordInstance();
		$ProductsInventoryBarcodes = Doctrine_Core::getTable('ProductsInventoryBarcodes')->getRecordInstance();
		
		$Products->hasMany('RentedProducts', array(
			'local' => 'products_id',
			'foreign' => 'products_id',
			'cascade' => array('delete')
		));

		$ProductsInventoryBarcodes->hasMany('RentedProducts', array(
			'local' => 'barcode_id',
			'foreign' => 'products_barcode',
			'cascade' => array('delete')
		));
		
		$Customers->hasMany('RentedProducts', array(
			'local' => 'customers_id',
			'foreign' => 'customers_id',
			'cascade' => array('delete')
		));
	}
	
	public function preInsert($event){
		$this->date_added = date('Y-m-d h:i:s');
	}
	
	public function setTableDefinition(){
		$this->setTableName('rented_products');
		$this->hasColumn('rented_products_id', 'integer', 4, array(
		'type' => 'integer',
		'length' => 4,
		'unsigned' => 0,
		'primary' => true,
		'autoincrement' => true,
		));
		$this->hasColumn('customers_id', 'integer', 4, array(
		'type' => 'integer',
		'length' => 4,
		'unsigned' => 0,
		'primary' => false,
		'default' => '0',
		'notnull' => true,
		'autoincrement' => false,
		));
		$this->hasColumn('products_id', 'integer', 4, array(
		'type' => 'integer',
		'length' => 4,
		'unsigned' => 0,
		'primary' => false,
		'default' => '0',
		'notnull' => true,
		'autoincrement' => false,
		));
		$this->hasColumn('date_added', 'timestamp', null, array(
		'type' => 'timestamp',
		'primary' => false,
		'default' => '0000-00-00 00:00:00',
		'notnull' => true,
		'autoincrement' => false,
		));
		$this->hasColumn('shipment_date', 'timestamp', null, array(
		'type' => 'timestamp',
		'primary' => false,
		'default' => '0000-00-00 00:00:00',
		'notnull' => true,
		'autoincrement' => false,
		));
		$this->hasColumn('arrival_date', 'timestamp', null, array(
		'type' => 'timestamp',
		'primary' => false,
		'default' => '0000-00-00 00:00:00',
		'notnull' => false,
		'autoincrement' => false,
		));
		$this->hasColumn('return_date', 'timestamp', null, array(
		'type' => 'timestamp',
		'primary' => false,
		'default' => '0000-00-00 00:00:00',
		'notnull' => false,
		'autoincrement' => false,
		));
		$this->hasColumn('products_barcode', 'string', 50, array(
		'type' => 'string',
		'length' => 50,
		'fixed' => false,
		'primary' => false,
		'default' => '0',
		'notnull' => true,
		'autoincrement' => false,
		));
		$this->hasColumn('broken', 'integer', 1, array(
		'type' => 'integer',
		'length' => 1,
		'unsigned' => 0,
		'primary' => false,
		'default' => '0',
		'notnull' => true,
		'autoincrement' => false,
		));
		$this->hasColumn('rental_status_id', 'integer', 4, array(
		'type' => 'integer',
		'length' => 4,
		'unsigned' => 0,
		'primary' => false,
		'notnull' => false,
		'autoincrement' => false,
		));
	}
}