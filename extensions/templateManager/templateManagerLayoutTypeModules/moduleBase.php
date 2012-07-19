<?php
class TemplateManagerLayoutTypeModuleBase extends ModuleBase
{

	protected $layoutId = 0;

	protected $layoutSettings = null;

	protected $_isPrint = false;

	/**
	 * @param string $code
	 * @param bool   $forceEnable
	 * @param bool   $moduleDir
	 */
	public function init($code, $forceEnable = false, $moduleDir = false)
	{
		$this->import(new Installable);

		$this->setModuleType('templateManagerLayoutType');
		parent::init($code, $forceEnable, $moduleDir);

		$this->isPrint(($this->getConfigData($this->getModuleInfo('is_print_key')) == 'True'));
	}

	/**
	 * @param null $val
	 * @return mixed
	 */
	public function isPrint($val = null)
	{
		if ($val !== null){
			$this->_isPrint = $val;
		}
		return $this->_isPrint;
	}

	public function setupLayout($Layout){
		$this->layoutId = $Layout->layout_id;
		$this->layoutSettings = json_decode($Layout->layout_settings);
	}

	public function getStartingLayoutPath()
	{
		return $this->getPath() . 'startingLayout/';
	}

	public function hasSetWidth()
	{
		return ($this->getSetWidth() !== null);
	}

	public function getSetWidth()
	{
		return null;
	}

	public function getLayoutTypeSelect()
	{
		$layoutTemplatesContainer = htmlBase::newElement('div');
		$Dir = new DirectoryIterator($this->getStartingLayoutPath());
		foreach($Dir as $d){
			if ($d->isFile() === true || $d->isDot() === true || strtolower($d->getBasename()) == 'codegeneration'){
				continue;
			}
			$sortedTemplates[] = $d->getBasename();
		}
		sort($sortedTemplates);
		foreach($sortedTemplates as $templateName){
			$Box = htmlBase::newElement('div')
				->css(array(
				'float'  => 'left',
				'margin' => '.5em'
			))
				->html('<center>' .
				'<input id="' . $templateName . '" type="radio" name="layout_template" value="' . $templateName . '"' . ($templateName == 'empty' ? ' checked=checked' : '') . '><br>' .
				'<label for="' . $templateName . '">' .
				'<img src="' . sysConfig::getDirWsCatalog() . str_replace(sysConfig::getDirFsCatalog(), '', $this->getStartingLayoutPath()) . $templateName . '/preview.png" height="200">' .
				'</label><br>' .
				ucfirst($templateName) . '<br>' .
				'</center>');

			$layoutTemplatesContainer->append($Box);
		}
		$layoutTemplatesContainer->append(htmlBase::newElement('div')->addClass('ui-helper-clearfix'));

		return $layoutTemplatesContainer;
	}

	public function onSave($Layout)
	{
	}
}