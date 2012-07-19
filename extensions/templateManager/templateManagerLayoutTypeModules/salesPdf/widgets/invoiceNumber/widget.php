<?php
/*
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class TemplateManagerWidgetInvoiceNumber extends TemplateManagerWidget
{

	public function __construct() {
		global $App;
		$this->init('invoiceNumber', false, __DIR__);
	}

	public function show(TemplateManagerLayoutBuilder $LayoutBuilder) {
		$boxWidgetProperties = $this->getWidgetProperties();
		$htmlText = '';
		$Sale = $LayoutBuilder->getVar('Sale');

		$Id = $Sale->InfoManager->getInfo('sale_id');
		switch($boxWidgetProperties->type){
			case 'top':
				$htmlText = $boxWidgetProperties->text . '<br/>' . $Id;
				break;
			case 'bottom':
				$htmlText = $Id . '<br/>' . $boxWidgetProperties->text;
				break;
			case 'left':
				$htmlText = $boxWidgetProperties->text . $Id;
				break;
			case 'right':
				$htmlText = $Id . $boxWidgetProperties->text;
				break;
		}
		$this->setBoxContent($htmlText);
		return $this->draw();
	}
}

?>