<?php
class InvoiceListingWidgetColumnTaxRate
{
	public function __construct(){
		$this->title = 'Products Tax Rate';
		$this->description = 'Displays the sale products tax rate';
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
		return sysCurrency::format(99);
	}

	public function getData(OrderProduct $SaleProduct){
		return $SaleProduct->getTaxRate();
	}
}