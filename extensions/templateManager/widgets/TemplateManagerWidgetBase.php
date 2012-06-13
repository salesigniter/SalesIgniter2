<?php
/*
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class TemplateManagerWidgetBase extends ModuleBase
{

	private $boxId = null;

	private $boxPath = null;

	private $boxHeadingText = null;

	private $boxHeadingLink = null;

	private $boxContent = null;

	private $boxTemplateDefaultDir = null;

	private $boxTemplateDefault = 'box.tpl';

	private $boxWidgetProperties = '';

	private $boxWidgetCss = null;

	private $boxTemplateFile = null;

	private $boxTemplateDir = null;

	private $extName = null;

	private $templateVars = array();

	protected $widgetDir = '';

	protected $widgetTemplateDir = '';

	public function init($moduleCode, $forceEnable = false, $moduleDir = false) {
		global $App;
		if ($moduleDir === false){
			$moduleDir = sysConfig::getDirFsCatalog() . 'extensions/templateManager/widgets/' . $this->widgetDir . '/widgets/' . $moduleCode;
		}
		parent::init($moduleCode, false, $moduleDir);

		$this->boxTemplateDefaultDir = sysConfig::getDirFsCatalog() . 'extensions/templateManager/widgets/' . $this->widgetDir . '/templates/';
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

	public function setWidgetCss($val){
		$this->boxWidgetCss = $val;
	}

	public function getWidgetCss(){
		return $this->boxWidgetCss;
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

	public function isTable(){
		return false;
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
		if (isset($WidgetSettings->widget_title_link) && $WidgetSettings->widget_title_link->type != 'none'){
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