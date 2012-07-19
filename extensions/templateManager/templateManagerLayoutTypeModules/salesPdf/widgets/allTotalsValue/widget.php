<?php
/*
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class TemplateManagerWidgetAllTotalsValue extends TemplateManagerWidget
{

	public function __construct() {
		global $App;
		$this->init('allTotalsValue', false, __DIR__);
	}

	public function show(TemplateManagerLayoutBuilder $LayoutBuilder) {
		global $currencies;
		$boxWidgetProperties = $this->getWidgetProperties();
		$htmlText = '';
		$Sale = $LayoutBuilder->getVar('Sale');

		foreach($Sale->TotalManager->getAll() as $Module){
			$totalTitle = $Module->getTitle();
			$totalValue = $currencies->format($Module->getValue());

			$htmlText .= '<div style="margin-top:7px;">';
			switch($boxWidgetProperties->type){
				case 'top':
					$htmlText .= $totalTitle . '<br/>' . $totalValue;
					break;
				case 'bottom':
					$htmlText .= $totalValue . '<br/>' . $totalTitle;
					break;
				case 'left':
					$htmlText .= $totalTitle . '  ' . $totalValue;
					break;
				case 'right':
					$htmlText .= $totalValue . '  ' . $totalTitle;
					break;
			}
			$htmlText .= '</div>';
		}
		$this->setBoxContent($htmlText);
		return $this->draw();
	}
}

?>