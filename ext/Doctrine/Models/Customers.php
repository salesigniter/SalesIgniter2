<?php

/**
 * Customers
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6401 2009-09-24 16:12:04Z guilhermeblanco $
 */
class Customers extends Doctrine_Record {
	
	public function setUp(){
		parent::setUp();

		//$this->setAttribute(Doctrine_Core::ATTR_VALIDATE, true);
		
		$this->hasMany('AddressBook', array(
			'local' => 'customers_id',
			'foreign' => 'customers_id',
			'cascade' => array('delete')
		));

		$this->hasMany('Orders', array(
			'local' => 'customers_id',
			'foreign' => 'customers_id',
			'cascade' => array('delete')
		));

		$this->hasOne('CustomersMembership', array(
				'local' => 'customers_id',
				'foreign' => 'customers_id',
				'cascade' => array('delete')
			));

		$this->hasMany('MembershipBillingReport', array(
				'local' => 'customers_id',
				'foreign' => 'customers_id'
			));

		$this->hasOne('CustomersInfo', array(
				'local' => 'customers_id',
				'foreign' => 'customers_info_id',
				'cascade' => array('delete')
			));
	}
	
	public function setTableDefinition(){
		$this->setTableName('customers');
		
		$this->hasColumn('customers_id', 'integer', 4, array(
			'type' => 'integer',
			'length' => 4,
			'unsigned' => 0,
			'primary' => true,
			'autoincrement' => true,
		));
		$this->hasColumn('customers_number', 'string', 12, array(
			'type' => 'string',
			'length' => 12
		));
		$this->hasColumn('customers_account_frozen', 'integer', 1, array(
			'type' => 'integer',
			'length' => 1,
			'default' => 0,
			'primary' => false,
			'autoincrement' => false,
		));
		$this->hasColumn('language_id', 'integer', 4, array(
			'type' => 'integer',
			'length' => 4,
			'unsigned' => 0,
			'primary' => false,
			'autoincrement' => false,
		));
		$this->hasColumn('customers_gender', 'string', 1, array(
			'type' => 'string',
			'length' => 1,
			'fixed' => true,
			'primary' => false,
			'default' => '',
			'notnull' => true,
			'autoincrement' => false,
		));
		$this->hasColumn('customers_firstname', 'string', 32, array(
			'notblank'   => true,
			'minlength' => 3,
			'type' => 'string',
			'length' => 32,
			'default' => '',
			'notnull' => true
		));
		$this->hasColumn('customers_lastname', 'string', 32, array(
			'notblank'   => true,
			'minlength' => 3,
			'type' => 'string',
			'length' => 32,
			'default' => '',
			'notnull' => true
		));
		$this->hasColumn('customers_dob', 'date', null, array(
			'past' => true,
			'type' => 'date',
			'default' => '0000-00-00',
			'notnull' => true
		));
		$this->hasColumn('customers_email_address', 'string', 96, array(
			'unique' => true,
			'notblank'   => true,
			'email'   => true,
			'type' => 'string',
			'length' => 96,
			'default' => '',
			'notnull' => true
		));
		$this->hasColumn('customers_default_address_id', 'integer', 4, array(
			'type' => 'integer',
			'length' => 4,
			'unsigned' => 0,
			'primary' => false,
			'notnull' => false,
			'autoincrement' => false,
		));
		$this->hasColumn('customers_delivery_address_id', 'integer', 4, array(
			'type' => 'integer',
			'length' => 4,
			'unsigned' => 0,
			'primary' => false,
			'notnull' => false,
			'autoincrement' => false,
		));
		$this->hasColumn('customers_telephone', 'string', 32, array(
			'type' => 'string',
			'length' => 32,
			'fixed' => false,
			'primary' => false,
			'default' => '',
			'notnull' => true,
			'autoincrement' => false,
		));
		$this->hasColumn('customers_city_birth', 'string', 250, array(
				'type' => 'string',
				'length' => 250,
				'fixed' => false,
				'primary' => false,
				'default' => '',
				'notnull' => true,
				'autoincrement' => false,
			));

		$this->hasColumn('customers_notes', 'string', null, array(
				'type' => 'string',
				'length' => null,
				'fixed' => false,
				'primary' => false,
				'default' => '',
				'notnull' => false,
				'autoincrement' => false,
			));
		$this->hasColumn('customers_fax', 'string', 32, array(
			'type' => 'string',
			'length' => 32,
			'fixed' => false,
			'primary' => false,
			'notnull' => false,
			'autoincrement' => false,
		));
		$this->hasColumn('customers_password', 'string', 40, array(
			'type' => 'string',
			'length' => 40,
			'fixed' => false,
			'primary' => false,
			'default' => '',
			'notnull' => true,
			'autoincrement' => false,
		));
		$this->hasColumn('customers_newsletter', 'string', 1, array(
			'type' => 'string',
			'length' => 1,
			'fixed' => true,
			'primary' => false,
			'notnull' => false,
			'autoincrement' => false,
		));
	}
}