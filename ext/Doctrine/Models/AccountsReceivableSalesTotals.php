<?php

class AccountsReceivableSalesTotals extends Doctrine_Record {

	public function setUp(){
	}

	public function setTableDefinition(){
		$this->setTableName('accounts_receivable_sales_totals');

		$this->hasColumn('sale_id', 'integer', 4);
		$this->hasColumn('module_code', 'string', 128);
		$this->hasColumn('total_value', 'decimal', 15, array(
			'scale' => 4
		));
		$this->hasColumn('display_order', 'integer', 2);
		$this->hasColumn('total_json', 'string', 999);
	}
}