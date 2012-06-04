<?php
/*
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class TemplateManagerPrintWidgetGateNumber extends TemplateManagerPrintWidget
{

	public function __construct() {
		global $App;
		$this->init('gateNumber');
	}

	public function show(TemplateManagerLayoutBuilder $LayoutBuilder) {
		$gateNr = isset($_GET['gateNr']) ? $_GET['gateNr'] : '';
		$gate = 'Gate No.:';
		if (!empty($gateNr)){
			$gate .= $gateNr;
		}
		$boxWidgetProperties = $this->getWidgetProperties();

		$this->setBoxContent($gate);
		return $this->draw();
	}
}

?>