<?php
/**
 * Sales Igniter E-Commerce System
 * Version: {ses_version}
 *
 * I.T. Web Experts
 * http://www.itwebexperts.com
 *
 * Copyright (c) {ses_copyright} I.T. Web Experts
 *
 * This script and its source are not distributable without the written consent of I.T. Web Experts
 */

class TemplateManagerWidgetCustomPhp extends TemplateManagerWidget
{

	public function __construct()
	{
		global $App;
		$this->init('customPhp');
	}

	public function show(TemplateManagerLayoutBuilder $LayoutBuilder)
	{
		global $appExtension, $shoppingCart;
		$boxWidgetProperties = $this->getWidgetProperties();
		$htmlCode = $boxWidgetProperties->php_text;

		ob_start();
		eval("?>" . $htmlCode);
		$htmlText = ob_get_contents();
		ob_end_clean();
		$this->setBoxContent($htmlText);
		return $this->draw();
	}
}

?>