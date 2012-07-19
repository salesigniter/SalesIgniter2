<?php
/*
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class TemplateManagerWidgetSaleNumber extends TemplateManagerWidget
{

	public function __construct() {
		global $App;
		$this->init('saleNumber', false, __DIR__);
	}

	public function show(TemplateManagerLayoutBuilder $LayoutBuilder) {
		global $currencies;
		$boxWidgetProperties = $this->getWidgetProperties();
		$htmlText = '';
		$Sale = $LayoutBuilder->getVar('Sale');

		$Id = $Sale->InfoManager->getInfo('sale_id');

		$IdText = '#' . $Id;
		if ($boxWidgetProperties->showRevisionNumber === true){
			$Rev = $Sale->InfoManager->getInfo('revision');
			$IdText .= '.R' . $Rev;
		}

		if (!empty($boxWidgetProperties->text)){
			switch($boxWidgetProperties->type){
				case 'top':
					$htmlText = $boxWidgetProperties->text . '<br/>' . $IdText;
					break;
				case 'bottom':
					$htmlText = $IdText . '<br/>' . $boxWidgetProperties->text;
					break;
				case 'left':
					$htmlText = $boxWidgetProperties->text . $IdText;
					break;
				case 'right':
					$htmlText = $IdText . $boxWidgetProperties->text;
					break;
			}
		}else{
			$htmlText = $IdText;
		}

		$this->setBoxContent($htmlText);
		return $this->draw();
	}
}

?>