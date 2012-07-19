<?php
class InvoiceListingWidgetColumnTaxCharged
{
	public function __construct(){
		$this->title = 'Products Tax Charged';
		$this->description = 'Displays the tax charged for the sale product';
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
		return sysCurrency::format($SaleProduct->getPrice(true) - $SaleProduct->getPrice());
	}
}