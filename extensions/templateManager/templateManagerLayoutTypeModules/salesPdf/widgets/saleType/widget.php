<?php
/*
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class TemplateManagerWidgetSaleType extends TemplateManagerWidget
{

	public function __construct() {
		global $App;
		$this->init('saleType', false, __DIR__);
	}

	public function showLayoutPreview($WidgetSettings)
	{
		global $appExtension;
		AccountsReceivableModules::loadModules();
		$htmlText = array();
		foreach(AccountsReceivableModules::getModules() as $Module){
			$htmlText[] = $Module->getTitle();
		}

		$return = implode(' / ', $htmlText);
		return $return;
	}


	public function show(TemplateManagerLayoutBuilder $LayoutBuilder) {
		global $currencies;
		$boxWidgetProperties = $this->getWidgetProperties();
		$htmlText = '';
		$Sale = $LayoutBuilder->getVar('Sale');

		$htmlText = $Sale->getSaleModule()->getTitle();
		$this->setBoxContent($htmlText);
		return $this->draw();
	}
}

?>