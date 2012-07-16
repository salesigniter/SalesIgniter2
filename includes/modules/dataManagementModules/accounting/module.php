<?php
class DataManagementModuleAccounting extends DataManagementModuleBase
{

	public function __construct()
	{
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Accounts Receivable Data Management');
		$this->setDescription('Import And Export Sales Using This Module');

		$this->init(
			'accounting',
			true,
			__DIR__
		);
	}

	public function getSupportedActions(){
		return array(
			'export' => 'Export File'
		);
	}

	public function getSupportedColumns()
	{
		$supportedColumns = array(
			'v_sale_id'                 => true,
			'v_sale_type'               => true,
			'v_customers_email_address' => true,
			'v_customers_telephone'     => true,
			//'v_payment_method'          => true,
			'v_status'                  => true,
			'v_date_purchased'          => true,
			'v_customers_address'       => true,
			'v_billing_address'         => true,
			'v_delivery_address'        => true,
			'v_pickup_address'          => true,
			'v_products'                => true,
			'v_totals'                  => true,
		);

		EventManager::notify('AdminOrdersListingExportFields', &$supportedColumns);

		return $supportedColumns;
	}

	public function runExport($Ids = array(), $Columns = array())
	{
		$ExportFile = $this->getExportFileWriter();

		$HeaderRow = $ExportFile->newHeaderRow();
		$addColumns = $this->getSupportedColumns();

		if (is_array($Columns) && sizeof($Columns) > 0){
			foreach($addColumns as $k => $v){
				$addColumns[$k] = false;
			}
			foreach($Columns as $colName){
				if (isset($addColumns[$colName])){
					$addColumns[$colName] = true;
				}
			}
		}
		foreach($addColumns as $k => $include){
			if ($include === true){
				$HeaderRow->addColumn($k);
			}
		}

		if (empty($Ids)){
			$Sales = AccountsReceivable::getSales();
		}
		else {
			foreach($Ids as $SaleId){
				$Sales[] = AccountsReceivable::getSale(null, $SaleId);
			}
		}
		//echo '<pre>';print_r($HeaderRow->getRefArray());

		foreach($Sales as $Sale){
			$CurrentRow = $ExportFile->newRow();

			if ($addColumns['v_sale_id'] === true){
				$CurrentRow->addColumn($Sale->getSaleId(), 'v_sale_id');
			}
			if ($addColumns['v_sale_type'] === true){
				$CurrentRow->addColumn($Sale
					->getSaleModule()
					->getCode(), 'v_sale_type');
			}
			/*if ($addColumns['v_payment_method'] === true){
				$CurrentRow->addColumn($Sale->getPaymentMethod(), 'v_payment_method');
			}*/
			if ($addColumns['v_status'] === true){
				$CurrentRow->addColumn($Sale->getStatusName(), 'v_status');
			}
			if ($addColumns['v_date_purchased'] === true){
				$CurrentRow->addColumn($Sale->getDateAdded(), 'v_date_purchased');
			}
			if ($addColumns['v_customers_email_address'] === true){
				$CurrentRow->addColumn($Sale->getEmailAddress(), 'v_customers_email_address');
			}
			if ($addColumns['v_customers_telephone'] === true){
				$CurrentRow->addColumn($Sale->getTelephone(), 'v_customers_telephone');
			}

			if ($addColumns['v_totals'] === true){
				$Sale->TotalManager->onExport($addColumns, $CurrentRow, $HeaderRow);
			}

			$Sale->AddressManager->onExport($addColumns, $CurrentRow, $HeaderRow);

			if ($addColumns['v_products'] === true){
				$Sale->ProductManager->onExport($addColumns, $CurrentRow, $HeaderRow);
			}
		}

		$HeaderRowRef = $HeaderRow->getRefArray();
		$HeaderRow->removeColumn($HeaderRowRef['v_products']);
		$HeaderRow->removeColumn($HeaderRowRef['v_totals']);
		$HeaderRow->removeColumn($HeaderRowRef['v_customers_address']);
		$HeaderRow->removeColumn($HeaderRowRef['v_billing_address']);
		$HeaderRow->removeColumn($HeaderRowRef['v_delivery_address']);
		$HeaderRow->removeColumn($HeaderRowRef['v_pickup_address']);

		//echo '<pre>';print_r($HeaderRow->getRefArray());
		$ExportFile->output();
	}
}
