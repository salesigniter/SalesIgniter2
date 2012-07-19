<?php
class InvoiceListingWidgetColumnNameExtra
{
	public function __construct(){
		$this->title = 'Products Name Extra';
		$this->description = 'Displays the sale products name with extra information included';
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
		return 'Demo Product Name<br> - Extra Information';
	}

	public function getData(OrderProduct $SaleProduct){
		return $SaleProduct->getNameHtml(true);
	}
}