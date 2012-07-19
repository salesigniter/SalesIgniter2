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

class TemplateManagerWidgetPageButtons extends TemplateManagerWidget
{

	public function __construct()
	{
		$this->init('pageButtons', false, __DIR__);
	}

	public function show(TemplateManagerLayoutBuilder $LayoutBuilder)
	{
		global $Template;
		/* Page Content is the only widget that parses directly into its tpl file */
		$PageButtons = $Template->getVar('pageContent')->getVar('pageButtons');
		$this->setBoxContent($PageButtons);

		return $this->draw();
	}
}

?>