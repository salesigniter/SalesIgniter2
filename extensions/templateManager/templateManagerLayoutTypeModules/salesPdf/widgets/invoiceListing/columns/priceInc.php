<?php
class InvoiceListingWidgetColumnPriceInc
{
	public function __construct(){
		$this->title = 'Products Price';
		$this->description = 'Displays the sale products price each including tax';
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
		return sysCurrency::format($SaleProduct->getPrice(true));
	}
}