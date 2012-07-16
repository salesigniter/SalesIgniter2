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

class TemplateManagerWidgetBreadcrumb extends TemplateManagerWidget
{

	public function __construct()
	{
		global $App;
		$this->init('breadcrumb');
	}

	public function show(TemplateManagerLayoutBuilder $LayoutBuilder)
	{
		global $breadcrumb;

		$this->setBoxContent('<div class="breadcrumbTrail">' . $breadcrumb->trail(' &raquo; ') . '</div>');

		return $this->draw();

		return false;
	}
}

?>