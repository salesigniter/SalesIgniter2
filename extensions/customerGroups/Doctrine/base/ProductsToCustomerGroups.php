<?php

/**
 * CustomersToCustomerGroups
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6401 2009-09-24 16:12:04Z guilhermeblanco $
 */
class ProductsToCustomerGroups extends Doctrine_Record {

	public function setUp(){
		$this->setUpParent();
		
		$this->hasOne('Products', array(
			'local' => 'products_id',
			'foreign' => 'products_id'
		));
		
		$this->hasOne('CustomerGroups', array(
			'local' => 'customer_groups_id',
			'foreign' => 'customer_groups_id'
		));
	}

	public function setUpParent(){
		$CustomersGroups = Doctrine::getTable('CustomerGroups')->getRecordInstance();
		$Products = Doctrine::getTable('Products')->getRecordInstance();
		
		$CustomersGroups->hasMany('ProductsToCustomerGroups', array(
			'local' => 'customer_groups_id',
			'foreign' => 'customer_groups_id',
			'cascade' => array('delete')
		));
		$Products->hasMany('ProductsToCustomerGroups', array(
			'local' => 'products_id',
			'foreign' => 'products_id',
			'cascade' => array('delete')
		));
	}

	public function setTableDefinition(){
		$this->setTableName('products_to_customer_groups');
		
		$this->hasColumn('products_id', 'integer', 4, array(
			'type' => 'integer',
			'length' => 4,
			'unsigned' => 0,
			'primary' => true,
			'autoincrement' => false,
		));
		
		$this->hasColumn('customer_groups_id', 'integer', 4, array(
			'type' => 'integer',
			'length' => 4,
			'unsigned' => 0,
			'primary' => true,
			'autoincrement' => false,
		));
	}
}