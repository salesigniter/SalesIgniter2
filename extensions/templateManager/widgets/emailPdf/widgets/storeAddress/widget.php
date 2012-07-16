<?php
/*
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class TemplateManagerEmailPdfWidgetStoreAddress extends TemplateManagerEmailPdfWidget
{

	public function __construct() {
		global $App;
		$this->init('storeAddress');
	}

	public function show(TemplateManagerLayoutBuilder $LayoutBuilder) {
		$boxWidgetProperties = $this->getWidgetProperties();

		$htmlText = nl2br(sysConfig::get('STORE_NAME_ADDRESS')) . '<br/>';
		if ($boxWidgetProperties->email){
			$htmlText .= sysConfig::get('STORE_EMAIL_ADDRESS') . '<br/>';
		}

		if ($boxWidgetProperties->website){
			$htmlText .= sysConfig::get('HTTP_DOMAIN_NAME') . '<br/>';
		}

		$this->setBoxContent($htmlText);
		return $this->draw();
	}
}

?>