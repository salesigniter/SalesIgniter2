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

class TemplateManagerWidgetQuickSearch extends TemplateManagerWidget
{

	public function __construct()
	{
		global $App;
		$this->init('quickSearch');
		$this->buildStylesheetMultiple = false;
		$this->buildJavascriptMultiple = false;
	}

	public function show(TemplateManagerLayoutBuilder $LayoutBuilder)
	{
		$buttonGo = htmlBase::newElement('button')
			->addClass('quickSearchGoButton')
			->setType('submit')
			->setText(' Go ')
			->draw();
		$searchField = htmlBase::newElement('input')
			->setLabel(sysLanguage::get('WIDGET_QUICK_SEARCH_LABEL'))
			->setLabelPosition('before')
			->setName('keywords')
			->addClass('quickSearchInput')
			->setType('text')
			->setSize('20')
			->draw();

		$boxForm = htmlBase::newElement('form')
			->attr('name', 'quick_find')
			->attr('action', itw_app_link(null, 'products', 'search_result'))
			->attr('method', 'get');

		$boxContent = tep_hide_session_id();
		$boxContent .= htmlBase::newElement('span')
			->addClass('quickSearchLabel')
			->text(sysLanguage::get('WIDGET_QUICK_SEARCH_TEXT') .
			'<br><a href="' . itw_app_link(null, 'products', 'search') . '"><b>' . sysLanguage::get('WIDGET_SEARCH_ADVANCED_SEARCH') . '</b></a>' .
			'<br />')
			->draw();
		$boxContent .= $searchField;
		$boxContent .= $buttonGo;

		$boxForm->html($boxContent);

		$this->setBoxContent($boxForm->draw());
		return $this->draw();
	}
}

?>