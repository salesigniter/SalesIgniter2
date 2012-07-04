<?php

class AccountsReceivableSalesTransactions extends Doctrine_Record {

	public function preSave($event){
		if (is_array($this->transaction_data)){
			$this->transaction_data = json_encode($this->transaction_data);
		}
	}

	public function setTableDefinition(){
		$this->setTableName('accounts_receivable_sales_transactions');

		$this->hasColumn('sale_id', 'integer', 4);
		$this->hasColumn('transaction_data', 'string', 999);
	}
}