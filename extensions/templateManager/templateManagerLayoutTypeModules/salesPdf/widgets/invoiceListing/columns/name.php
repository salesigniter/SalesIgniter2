<?php
class InvoiceListingWidgetColumnName
{
	public function __construct(){
		$this->title = 'Products Name';
		$this->description = 'Displays the sale products name';
	}

	public function getTitle(){
		return $this->title;
	}

	public function getDescription(){
		return $this->description;
	}

	public function getCode(){
		return basename(__FILE__, '.php');
	}

	public function getPreviewData(){
		return 'Demo Product Name';
	}

	public function getData(OrderProduct $SaleProduct){
		return $SaleProduct->getName();
	}
}