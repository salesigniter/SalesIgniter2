<?php
/*
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class TemplateManagerWidgetInsuranceValue extends TemplateManagerWidget
{

	public function __construct() {
		global $App;
		$this->init('insuranceValue', false, __DIR__);
	}

	public function showLayoutPreview($WidgetSettings)
	{
		$htmlText = '';

		$amount = sysCurrency::format('9999999.99');

		if (!empty($WidgetSettings['settings']->text)){
			switch($WidgetSettings['settings']->type){
				case 'top':
					$htmlText = $WidgetSettings['settings']->text . '<br/>' . $amount;
					break;
				case 'bottom':
					$htmlText = $amount . '<br/>' . $WidgetSettings['settings']->text;
					break;
				case 'left':
					$htmlText = $WidgetSettings['settings']->text . $amount;
					break;
				case 'right':
					$htmlText = $amount . $WidgetSettings['settings']->text;
					break;
			}
		}else{
			$htmlText = $amount;
		}
		return $htmlText;
	}

	public function show(TemplateManagerLayoutBuilder $LayoutBuilder) {
		global $currencies;
		$boxWidgetProperties = $this->getWidgetProperties();
		$htmlText = '';
		$Sale = $LayoutBuilder->getVar('Sale');

		$insuranceTotal = 0;
		foreach($Sale->ProductManager->getContents() as $Product){
			$insuranceTotal += $Product->getInfo('insurance_value');
		}

		$this->setBoxContent(sysCurrency::format($insuranceTotal));
		return $this->draw();
	}
}

?>