<?php
/*
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class TemplateManagerPrintWidgetCouponValue extends TemplateManagerPrintWidget
{

	public function __construct() {
		global $App;
		$this->init('couponValue');
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

		$Coupon = $Sale->TotalManager->get('coupon');
		if ($Coupon !== null){
			$CouponValue = $currencies->format($Coupon->getValue());

			switch($boxWidgetProperties->type){
				case 'top':
					$htmlText = $boxWidgetProperties->text . '<br/>-' . $CouponValue;
					break;
				case 'bottom':
					$htmlText = '-' . $CouponValue . '<br/>' . $boxWidgetProperties->text;
					break;
				case 'left':
					$htmlText = $boxWidgetProperties->text . '-' . $CouponValue;
					break;
				case 'right':
					$htmlText = '-' . $CouponValue . $boxWidgetProperties->text;
					break;
			}
		}
		$this->setBoxContent($htmlText);
		return $this->draw();
	}
}

?>