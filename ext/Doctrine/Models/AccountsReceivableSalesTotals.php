<?php

class AccountsReceivableSalesTotals extends Doctrine_Record
{

	public function preHydrate($event)
	{
		$data = $event->data;
		if (isset($data['total_json'])){
			$data['total_json'] = json_decode($data['total_json'], true);
			$event->data = $data;
		}
	}

	public function preSave($event)
	{
		if (is_array($this->total_json)){
			$this->total_json = json_encode($this->total_json);
		}
	}

	public function setTableDefinition()
	{
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