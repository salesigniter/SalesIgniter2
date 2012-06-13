<?php

class AccountsReceivableSales extends Doctrine_Record {

	public function setUp(){
		/*$this->hasMany('SystemStatuses', array(
			'local' => 'sale_status_id',
			'foreign' => 'status_id',
			'cascade' => array('delete')
		));*/

		$this->hasMany('AccountsReceivableSalesProducts as Products', array(
			'local' => 'id',
			'foreign' => 'sale_id',
			'orderBy' => 'id',
			'cascade' => array('delete')
		));

		$this->hasMany('AccountsReceivableSalesTotals as Totals', array(
			'local' => 'id',
			'foreign' => 'sale_id',
			'orderBy' => 'display_order',
			'cascade' => array('delete')
		));
	}

	public function setTableDefinition(){
		$this->setTableName('accounts_receivable_sales');

		$this->hasColumn('sale_module', 'string', 64);
		$this->hasColumn('sale_id', 'integer', 4);
		$this->hasColumn('sale_status_id', 'integer', 4);
		$this->hasColumn('sale_revision', 'integer', 4);
		$this->hasColumn('sale_most_current', 'integer', 1);
		$this->hasColumn('customers_id', 'integer', 4);
		$this->hasColumn('customers_firstname', 'string', 128);
		$this->hasColumn('customers_lastname', 'string', 128);
		$this->hasColumn('customers_email_address', 'string', 128);
		$this->hasColumn('sale_total', 'decimal', 15, array(
			'scale' => 4
		));

		$this->hasColumn('date_added', 'timestamp');
		$this->hasColumn('date_modified', 'timestamp');

		$this->hasColumn('converted_from_module', 'string', 128);
		$this->hasColumn('converted_from_id', 'integer', 4);

		$this->hasColumn('info_json', 'string', 999);
		$this->hasColumn('address_json', 'string', 999);
		$this->hasColumn('totals_json', 'string', 999);
	}
}