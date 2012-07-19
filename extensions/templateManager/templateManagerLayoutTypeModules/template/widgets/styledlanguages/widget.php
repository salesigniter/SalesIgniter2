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

class TemplateManagerWidgetStyledLanguages extends TemplateManagerWidget
{

	public function __construct()
	{
		global $App;
		$this->init('styledlanguages', false, __DIR__);
	}

	public function show(TemplateManagerLayoutBuilder $LayoutBuilder)
	{
		global $request_type;

		$boxContent = '<ul>';
		foreach(sysLanguage::getLanguages() as $lInfo){
			$boxContent .= '
			    <li class="lang-' . $lInfo['code'] . '">
				<a href="' . itw_app_link(tep_get_all_get_params(array('language', 'currency')) . 'language=' . $lInfo['code']) . '"><span>'
				. $lInfo['name_real'] .
				'</span></a>
			    </li>';
		}
		$boxContent .= '</ul>';
		$this->setBoxContent($boxContent);

		return $this->draw();
	}
}

?>