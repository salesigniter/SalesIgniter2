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

class TemplateManagerWidgetPageStack extends TemplateManagerWidget {

	public function __construct(){
		global $App;
		$this->init('pageStack');

		$this->setBoxHeading(sysLanguage::get('WIDGET_HEADING_PAGE_STACK_OUTPUT'));
	}

	public function show(){
		    global $messageStack;
		    $pageStackOutput = ($messageStack->size('pageStack') > 0 ? $messageStack->output('pageStack') : '');
			$this->setBoxContent($pageStackOutput);

			return $this->draw();

			return false;
	}
}
?>