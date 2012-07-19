<?php
class InvoiceListingWidgetColumnPriceFinalInc
{
	public function __construct(){
		$this->title = 'Products Final Price Including Tax';
		$this->description = 'Displays the sale products price each X products quantity including tax';
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
		return sysCurrency::format($SaleProduct->getFinalPrice(true, true));
	}
}