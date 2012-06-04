<?php
/*
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class TemplateManagerPrintWidgetCustomCheckboxes extends TemplateManagerPrintWidget
{

	public function __construct() {
		global $App;
		$this->init('customCheckboxes');
	}

	public function show(TemplateManagerLayoutBuilder $LayoutBuilder) {
		global $currencies;
		$boxWidgetProperties = $this->getWidgetProperties();
		$htmlText = '';
		//$Sale = $LayoutBuilder->getVar('Sale');

		$myText = explode("\n", $boxWidgetProperties->text);
		$imgPath = '<img src="' . sysConfig::getDirWsCatalog() . 'includes/modules/pdfinfoboxes/customCheckboxes/images/checkbox.png"/>';
		$delim = '<br/>';
		if ($boxWidgetProperties->sameline){
			$delim = '';
		}
		foreach($myText as $text){
			switch($boxWidgetProperties->type){
				case 'left':
					$htmlText .= $imgPath . $text . $delim;
					break;
				case 'right':
					$htmlText .= $text . $imgPath . $delim;
					break;
			}
		}

		if (!empty($boxWidgetProperties->other)){
			switch($boxWidgetProperties->type){
				case 'left':
					$htmlText .= $imgPath . $boxWidgetProperties->other . '_______________' . $delim;
					break;
				case 'right':
					$htmlText .= $boxWidgetProperties->other . '_______________' . $imgPath . $delim;
					break;
			}
		}

		$this->setBoxContent($htmlText);
		return $this->draw();
	}
}

?>