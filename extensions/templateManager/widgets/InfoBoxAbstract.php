<?php
/*
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

abstract class InfoBoxAbstract
{

	private $boxId = null;

	private $boxPath = null;

	private $boxHeadingText = null;

	private $boxHeadingLink = null;

	private $boxContent = null;

	private $installed = false;

	private $boxTemplateDefaultDir = null;

	private $boxTemplateDefault = 'box.tpl';

	private $boxWidgetProperties = '';

	private $boxTemplateFile = null;

	private $boxTemplateDir = null;

	private $extName = null;

	private $templateVars = array();

	public function init($boxCode, $boxPath = null) {
		global $App;
		$this->boxCode = $boxCode;
		$this->boxTemplateDefaultDir = sysConfig::getDirFsCatalog() . 'extensions/templateManager/widgetTemplates/';

		if (is_null($boxPath) === false){
			$widgetPath = $boxPath . '/';
		}
		else {
			$widgetPath = sysConfig::getDirFsCatalog() . 'extensions/templateManager/widgets/' . $this->boxCode . '/';
		}
		$langPath = $widgetPath . '/language_defines/global.xml';
		$DoctPath = $widgetPath . '/Doctrine/base/';
		$overwritePath = 'includes/languages/' . Session::get('language') . '/' . str_replace('language_defines/', '', $langPath);

		$this->boxPath = $widgetPath;

		sysLanguage::loadDefinitions($langPath);
		sysLanguage::loadDefinitions($overwritePath);

		if (is_dir($DoctPath)){
			Doctrine_Core::loadModels($DoctPath, Doctrine_Core::MODEL_LOADING_AGGRESSIVE);
		}
	}

	public function getPath() {
		return $this->boxPath;
	}

	public function getExtName() {
		return $this->extName;
	}

	public function isInstalled() {
		return $this->installed;
	}

	public function getBoxCode() {
		return $this->boxCode;
	}

	public function setBoxTemplateFile($val) {
		$this->boxTemplateFile = $val;
	}

	public function setBoxTemplateDir($val) {
		$this->boxTemplateDir = $val;
	}

	public function setBoxHeading($val) {
		$this->boxHeadingText = $val;
	}

	public function setBoxHeadingLink($val) {
		$this->boxHeadingLink = $val;
	}

	public function setWidgetProperties($val) {
		$this->boxWidgetProperties = $val;
	}

	public function getWidgetProperties() {
		return $this->boxWidgetProperties;
	}

	public function setBoxContent($val) {
		$this->boxContent = $val;
	}

	public function setBoxId($val) {
		$this->boxId = $val;
	}

	public function getBoxTemplateFile() {
		return $this->boxTemplateFile;
	}

	public function getBoxTemplateDir() {
		return $this->boxTemplateDir;
	}

	public function getBoxHeading() {
		return $this->boxHeadingText;
	}

	public function getBoxHeadingLink() {
		return $this->boxHeadingLink;
	}

	public function getBoxContent() {
		return $this->boxContent;
	}

	public function show() {
		return $this->draw();
	}

	public function setTemplateVar($var, $val) {
		$this->templateVars[$var] = $val;
	}

	public function draw() {
		$WidgetSettings = $this->getWidgetProperties();

		$templateFile = $this->boxTemplateDefault;
		if (isset($WidgetSettings->template_file) && is_null($WidgetSettings->template_file) === false){
			$templateFile = $WidgetSettings->template_file;
		}

		$boxTemplate = new Template($templateFile, $this->boxTemplateDefaultDir);

		$this->templateVars['boxHeading'] = $this->boxHeadingText;
		if (isset($WidgetSettings->widget_title_link) && !empty($WidgetSettings->widget_title_link)){
			$Data = $WidgetSettings->widget_title_link;
			$itemLink = htmlBase::newElement('a')
				->html($this->templateVars['boxHeading']);

			if ($Data->type == 'app'){
				$getParams = null;
				if (stristr($Data->app->name, '/')){
					$extInfo = explode('/', $Data->app->name);
					$application = $extInfo[1];
					$getParams = 'appExt=' . $extInfo[0];
				}
				else {
					$application = $Data->app->name;
				}

				$itemLink->setHref(itw_app_link($getParams, $application, $Data->app->page));
			}
			elseif ($Data->type == 'category') {
				$itemLink->setHref(itw_app_link('cPath=' . str_replace('_none', '', implode('_', $Data->category->id)), $Data->app->name, $Data->app->page));
			}
			elseif ($Data->type == 'custom') {
				$itemLink->setHref($Data->url);
			}

			if ($Data->type != 'none'){
				if ($Data->target == 'new'){
					$itemLink->attr('target', '_blank');
				}
				elseif ($Data->target == 'dialog') {
					$itemLink->attr('onclick', 'Javascript:popupWindow(this.href);');
				}
			}
			$this->templateVars['boxHeading'] = $itemLink->draw();
		}

		$this->templateVars['boxContent'] = $this->boxContent;
		if (!is_null($this->boxId)){

			$this->templateVars['box_id'] = $this->boxId;
		}

		if (is_null($this->boxHeadingLink) === false){
			$link = htmlBase::newElement('a')
				->setHref($this->boxHeadingLink)
				->attr('alt', 'more')
				->attr('title', 'more')
				->addClass('ui-icon ui-icon-circle-triangle-e');

			$this->templateVars['boxLink'] = $link->draw();
		}

		$boxTemplate->setVars($this->templateVars);

		return $boxTemplate->parse();
	}
}

?>