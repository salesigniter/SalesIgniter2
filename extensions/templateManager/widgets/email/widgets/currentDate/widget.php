<?php
/*
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class TemplateManagerEmailWidgetCurrentDate extends TemplateManagerEmailWidget
{

	public function __construct()
	{
		global $App;
		$this->init('currentDate');
	}

	public function showLayoutPreview($WidgetSettings)
	{
		$boxWidgetProperties = $WidgetSettings['settings'];
		$htmlText = '';
		//$Sale = $LayoutBuilder->getVar('Sale');

		$curDate = new SesDateTime();
		if ($boxWidgetProperties->short == 'short'){
			$curDate = $curDate->format(sysLanguage::getDateFormat('short'));
		}
		else {
			$curDate = $curDate->format(sysLanguage::getDateFormat('long'));
		}

		$htmlText = $curDate;
		return $htmlText;
	}

	public function show(TemplateManagerLayoutBuilder $LayoutBuilder)
	{
		$boxWidgetProperties = $this->getWidgetProperties();
		$htmlText = '';
		//$Sale = $LayoutBuilder->getVar('Sale');

		$curDate = new SesDateTime();
		if ($boxWidgetProperties->short == 'short'){
			$curDate = $curDate->format(sysLanguage::getDateFormat('short'));
		}
		else {
			$curDate = $curDate->format(sysLanguage::getDateFormat('long'));
		}

		$htmlText = $curDate;

		$this->setBoxContent($htmlText);
		return $this->draw();
	}
}

?>