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

class TemplateManagerWidgetPageTitle extends TemplateManagerWidget
{

	public function __construct()
	{
		global $App;
		$this->init('pageTitle');
	}

	public function showLayoutPreview($WidgetSettings)
	{
		return 'Demo Page Title';
	}

	public function show()
	{
		global $Template, $pageContent;
		$this->setBoxContent('<h1 class="headingTitle">' . $pageContent->getVar('pageTitle') . '</h1>');
		return $this->draw();
	}
}

?>