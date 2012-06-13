<?php
/*
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class TemplateManagerLabelWidgetBarcode extends TemplateManagerLabelWidget
{

	public function __construct() {
		global $App;
		$this->init('barcode');
	}

	public function show(TemplateManagerLayoutBuilder $LayoutBuilder) {
		global $currencies;
		$boxWidgetProperties = $this->getWidgetProperties();
		$htmlText = '';

		$this->setBoxContent($htmlText);
		return $this->draw();
	}
}

?>