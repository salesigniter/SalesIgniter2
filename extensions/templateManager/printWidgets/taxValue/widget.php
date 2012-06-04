<?php
/*
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class TemplateManagerPrintWidgetTaxValue extends TemplateManagerPrintWidget
{

	public function __construct() {
		global $App;
		$this->init('taxValue');
	}

	public function showLayoutPreview($WidgetSettings){
		$htmlText = '';
		$widgetText = $WidgetSettings['settings']->text;

		switch($WidgetSettings['settings']->type){
			case 'top':
				$htmlText = $widgetText . '<br/>$0.00';
				break;
			case 'bottom':
				$htmlText = '$0.00<br/>' . $widgetText;
				break;
			case 'left':
				$htmlText = $widgetText . '$0.00';
				break;
			case 'right':
				$htmlText = '$0.00' . $widgetText;
				break;
		}

		$this->setBoxHeading($WidgetSettings['settings']->widget_title->{Session::get('languages_id')});
		if (isset($WidgetSettings['settings']->template_file) && is_null($WidgetSettings['settings']->template_file) === false){
			$templateFile = $WidgetSettings['settings']->template_file;
		}
		$boxTemplate = new Template($templateFile, sysConfig::getDirFsCatalog() . 'extensions/templateManager/printWidgetTemplates/');
		$boxTemplate->set('boxHeading', $this->getBoxHeading());
		$boxTemplate->set('boxContent', $htmlText);
		return $boxTemplate->parse();
	}

	public function show(TemplateManagerLayoutBuilder $LayoutBuilder) {
		global $currencies;
		$boxWidgetProperties = $this->getWidgetProperties();
		$htmlText = '';
		$Sale = $LayoutBuilder->getVar('Sale');

		$totalValue = $currencies->format($Sale->TotalManager->get('tax')->getValue());

		switch($boxWidgetProperties->type){
			case 'top':
				$htmlText = $boxWidgetProperties->text . '<br/>' . $totalValue;
				break;
			case 'bottom':
				$htmlText = $totalValue . '<br/>' . $boxWidgetProperties->text;
				break;
			case 'left':
				$htmlText = $boxWidgetProperties->text . $totalValue;
				break;
			case 'right':
				$htmlText = $totalValue . $boxWidgetProperties->text;
				break;
		}

		$this->setBoxContent($htmlText);
		return $this->draw();
	}
}

?>