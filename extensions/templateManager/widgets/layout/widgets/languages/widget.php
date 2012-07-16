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

class TemplateManagerWidgetLanguages extends TemplateManagerWidget
{

	public function __construct()
	{
		global $App;
		$this->init('languages');
	}

	public function show(TemplateManagerLayoutBuilder $LayoutBuilder)
	{
		$boxContent = '';
		foreach(sysLanguage::getLanguages() as $lInfo){
			$boxContent .= ' <a href="' . itw_app_link(tep_get_all_get_params(array('language', 'currency')) . 'language=' . $lInfo['code']) . '">' . $lInfo['showName']('&nbsp;') . '</a><br>';
		}

		$this->setBoxContent($boxContent);

		return $this->draw();
	}
}

?>