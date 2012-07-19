<?php
class InvoiceListingWidgetColumnSerials
{
	public function __construct(){
		$this->title = 'Products Serials';
		$this->description = 'Displays the sale products assigned serial numbers';
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
		return $SaleProduct->displayBarcodes();
	}
}