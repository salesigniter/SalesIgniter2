<?php
/*
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class TemplateManagerPrintWidgetPageCounter extends TemplateManagerPrintWidget
{

	public function __construct() {
		global $App;
		$this->init('pageCounter');
	}

	public function show(TemplateManagerLayoutBuilder $LayoutBuilder) {
		$this->setBoxContent('<span class="page-number"></span>');
		return $this->draw();
	}
}

?>