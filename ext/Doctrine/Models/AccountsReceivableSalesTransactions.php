<?php

class AccountsReceivableSalesTransactions extends Doctrine_Record {

	public function setTableDefinition(){
		$this->setTableName('accounts_receivable_sales_transactions');

		$this->hasColumn('sale_id', 'integer', 4);
		$this->hasColumn('transaction_data', 'string', 999);
	}
}