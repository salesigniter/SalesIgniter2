<?php
class TemplateManagerLayoutTypeModuleBase
{

	protected $layoutId = 0;

	protected $layoutSettings = null;

	public function __construct()
	{
	}

	public function setupLayout($Layout){
		$this->layoutId = $Layout->layout_id;
		$this->layoutSettings = json_decode($Layout->layout_settings);
	}

	public function getStartingLayoutPath()
	{
		return sysConfig::getDirFsCatalog() . $this->startingLayoutPath;
	}

	public function isEnabled()
	{
		return true;
	}

	public function getCode()
	{
		return $this->code;
	}

	public function getTitle()
	{
		return $this->title;
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
				'<img src="' . sysConfig::getDirWsCatalog() . $this->startingLayoutPath . $templateName . '/preview.png" height="200">' .
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