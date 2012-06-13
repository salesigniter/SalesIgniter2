<?php
/*
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class TemplateManagerPrintWidgetPageBreak extends TemplateManagerPrintWidget
{

	public function __construct() {
		global $App;
		$this->init('pageBreak');
	}

	public function show(TemplateManagerLayoutBuilder $LayoutBuilder) {
		$this->setBoxContent('<span style="page-break-after: always;"></span>');
		return $this->draw();
	}
}

?>