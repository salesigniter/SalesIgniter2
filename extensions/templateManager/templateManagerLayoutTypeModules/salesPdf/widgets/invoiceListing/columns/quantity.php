<?php
class InvoiceListingWidgetColumnQuantity
{
	public function __construct(){
		$this->title = 'Quantity';
		$this->description = 'Displays the sale products quantity';
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
		return '10';
	}

	public function getData(OrderProduct $SaleProduct){
		return $SaleProduct->getQuantity() . 'x';
	}
}