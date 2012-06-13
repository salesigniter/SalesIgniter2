<?php
class TemplateManagerLayoutBuilder
{

	protected $layoutId;

	protected $widgetDirectory = '';

	protected $widgetTemplateDirectory = '';

	protected $widgetPaths = array();

	protected $widgetTemplatePaths = array();

	protected $variables = array();

	public function __construct($layoutId = null) {
		if ($layoutId !== null){
			$this->setLayoutId($layoutId);
		}
	}

	public function addVar($varName, $varVal){
		$this->variables[$varName] = $varVal;
	}

	public function getVar($varName){
		return $this->variables[$varName];
	}

	public function setLayoutId($layoutId){
		$this->layoutId = $layoutId;
		$LayoutInfo = Doctrine_Manager::getInstance()
			->getCurrentConnection()
			->fetchAssoc('select * from template_manager_layouts where layout_id = "' . $layoutId . '"');
		$this->LayoutInfo = $LayoutInfo[0];

		if ($this->LayoutInfo['page_type'] == 'print'){
			$this->widgetDirectory = 'print';
			$this->widgetClassPrefix = 'TemplateManagerPrintWidget';
		}
		elseif ($this->LayoutInfo['page_type'] == 'email'){
			$this->widgetDirectory = 'email';
			$this->widgetClassPrefix = 'TemplateManagerEmailWidget';
		}
		elseif ($this->LayoutInfo['page_type'] == 'label'){
			$this->widgetDirectory = 'label';
			$this->widgetClassPrefix = 'TemplateManagerLabelWidget';
		}
		else {
			$this->widgetDirectory = 'layout';
			$this->widgetClassPrefix = 'TemplateManagerWidget';
		}

		$this->loadWidgets();
	}

	public function loadWidgets(){
		global $appExtension;
		$this->widgetPaths = array();
		$this->widgetTemplatePaths = array();

		$dir = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'extensions/templateManager/widgets/' . $this->widgetDirectory . '/widgets/');
		foreach($dir as $dInfo){
			if ($dInfo->isDot() || $dInfo->isFile()){
				continue;
			}
			$this->widgetPaths[$dInfo->getBasename()] = $dInfo->getPathname();
		}

		$dir = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'extensions/templateManager/widgets/' . $this->widgetDirectory . '/templates/');
		foreach($dir as $dInfo){
			if ($dInfo->isDot() || $dInfo->isDir()){
				continue;
			}
			$this->widgetTemplatePaths[$dInfo->getBasename('.tpl')] = $dInfo->getPathname();
		}

		$dir = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'extensions/');
		foreach($dir as $dInfo){
			if (
				$dInfo->isDot() ||
				$dInfo->isFile() ||
				$appExtension->isInstalled($dInfo->getBasename()) === false
			){
				continue;
			}

			if (is_dir($dInfo->getPathName() . '/catalog/ext_app/templateManager/widgets/' . $this->widgetDirectory . '/widgets/')){
				$subDir = new DirectoryIterator($dInfo->getPathName() . '/catalog/ext_app/templateManager/widgets/' . $this->widgetDirectory . '/widgets/');
				foreach($subDir as $sdInfo){
					if ($sdInfo->isDot() || $sdInfo->isFile()){
						continue;
					}
					$this->widgetPaths[$sdInfo->getBasename()] = $sdInfo->getPathname();
				}
			}

			if (is_dir($dInfo->getPathName() . '/catalog/ext_app/templateManager/widgets/' . $this->widgetDirectory . '/templates/')){
				$subDir = new DirectoryIterator($dInfo->getPathName() . '/catalog/ext_app/templateManager/widgets/' . $this->widgetDirectory . '/templates/');
				foreach($subDir as $sdInfo){
					if ($sdInfo->isDot() || $sdInfo->isDir()){
						continue;
					}
					$this->widgetTemplatePaths[$sdInfo->getBasename('.tpl')] = $sdInfo->getPathname();
				}
			}
		}

		$dir = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'templates/');
		foreach($dir as $dInfo){
			if ($dInfo->isDot() || $dInfo->isFile()){
				continue;
			}
			if (is_dir($dInfo->getPathname() . '/extensions/templateManager/widgets/' . $this->widgetDirectory . '/widgets/')){
				$subDir = new DirectoryIterator($dInfo->getPathName() . '/extensions/templateManager/widgets/' . $this->widgetDirectory . '/widgets/');
				foreach($subDir as $sdInfo){
					if ($sdInfo->isDot() || $sdInfo->isFile()){
						continue;
					}
					$this->widgetPaths[$sdInfo->getBasename()] = $sdInfo->getPathname();
				}
			}

			if (is_dir($dInfo->getPathname() . '/extensions/templateManager/widgets/' . $this->widgetDirectory . '/templates/')){
				$subDir = new DirectoryIterator($dInfo->getPathName() . '/extensions/templateManager/widgets/' . $this->widgetDirectory . '/templates/');
				foreach($subDir as $sdInfo){
					if ($sdInfo->isDot() || $sdInfo->isDir()){
						continue;
					}
					$this->widgetTemplatePaths[$sdInfo->getBasename('.tpl')] = $sdInfo->getPathname();
				}
			}
		}
		ksort($this->widgetPaths);
		ksort($this->widgetTemplatePaths);
	}

	public function getWidgets(){
		$widgets = array();
		foreach($this->getWidgetPaths() as $Code => $Path){
			$widgets[] = $this->getWidget($Code);
		}
		return $widgets;
	}

	public function getWidgetPaths(){
		return $this->widgetPaths;
	}

	public function getWidgetTemplatePaths(){
		return $this->widgetTemplatePaths;
	}

	private function loadWidget($code){
		if (isset($this->widgetPaths[$code])){
			if (class_exists($this->widgetClassPrefix . ucfirst($code)) === false){
				require($this->widgetPaths[$code] . '/widget.php');
			}
			return true;
		}
		return false;
	}

	public function getWidget($code){
		if ($this->loadWidget($code) !== false){
			$className = $this->widgetClassPrefix . ucfirst($code);
			return new $className;
		}
		return false;
	}

	public function getContainers($parentId = 0) {
		$Containers = Doctrine_Manager::getInstance()
			->getCurrentConnection()
			->fetchAssoc('select * from template_manager_layouts_containers where layout_id = "' . $this->layoutId . '" and parent_id = 0 order by sort_order');
		return $Containers;
	}

	public function getConfigInfo($type, $id) {
		if ($type == 'layout'){
			$idCol = 'layout_id';
			$table = 'template_manager_layouts_configuration';
		}
		elseif ($type == 'container'){
			$idCol = 'container_id';
			$table = 'template_manager_layouts_containers_configuration';
		}
		elseif ($type == 'column') {
			$idCol = 'column_id';
			$table = 'template_manager_layouts_columns_configuration';
		}
		elseif ($type == 'widget') {
			$idCol = 'widget_id';
			$table = 'template_manager_layouts_widgets_configuration';
		}

		$cfgInfo = false;
		$ResultSet = Doctrine_Manager::getInstance()
			->getCurrentConnection()
			->fetchAssoc('select * from ' . $table . ' where ' . $idCol . ' = "' . $id . '"');
		if (sizeof($ResultSet) > 0){
			$cfgInfo = array();
			foreach($ResultSet as $Result){
				$cfgInfo[] = $Result;
			}
		}
		return $cfgInfo;
	}

	public function getStyleInfo($type, $id) {
		if ($type == 'layout'){
			$idCol = 'layout_id';
			$table = 'template_manager_layouts_styles';
		}
		elseif ($type == 'container'){
			$idCol = 'container_id';
			$table = 'template_manager_layouts_containers_styles';
		}
		elseif ($type == 'column') {
			$idCol = 'column_id';
			$table = 'template_manager_layouts_columns_styles';
		}
		elseif ($type == 'widget') {
			$idCol = 'widget_id';
			$table = 'template_manager_layouts_widgets_styles';
		}

		$cssInfo = false;
		$ResultSet = Doctrine_Manager::getInstance()
			->getCurrentConnection()
			->fetchAssoc('select * from ' . $table . ' where ' . $idCol . ' = "' . $id . '"');
		if (sizeof($ResultSet) > 0){
			$cssInfo = array();
			foreach($ResultSet as $Result){
				$cssInfo[] = $Result;
			}
		}
		return $cssInfo;
	}

	public function getContainerColumns($id) {
		$Columns = false;
		$ResultSet = Doctrine_Manager::getInstance()
			->getCurrentConnection()
			->fetchAssoc('select * from template_manager_layouts_columns where container_id = "' . $id . '" and parent_id = 0 order by sort_order');
		if (sizeof($ResultSet) > 0){
			$Columns = array();
			foreach($ResultSet as $Result){
				$Columns[] = $Result;
			}
		}
		return $Columns;
	}

	public  function getColumnChildren($id){
		$Columns = false;
		$ResultSet = Doctrine_Manager::getInstance()
			->getCurrentConnection()
			->fetchAssoc('select * from template_manager_layouts_columns where parent_id = "' . $id . '" order by sort_order');
		if (sizeof($ResultSet) > 0){
			$Columns = array();
			foreach($ResultSet as $Result){
				$Columns[] = $Result;
			}
		}
		return $Columns;
	}

	public function getContainerChildren($id) {
		$Children = false;
		$ResultSet = Doctrine_Manager::getInstance()
			->getCurrentConnection()
			->fetchAssoc('select * from template_manager_layouts_containers where parent_id = "' . $id . '" order by sort_order');
		if (sizeof($ResultSet) > 0){
			$Children = array();
			foreach($ResultSet as $Result){
				$Children[] = $Result;
			}
		}
		return $Children;
	}

	public function getColumnWidgets($id) {
		$Widgets = false;
		$ResultSet = Doctrine_Manager::getInstance()
			->getCurrentConnection()
			->fetchAssoc('select * from template_manager_layouts_widgets where column_id = "' . $id . '" order by sort_order');
		if (sizeof($ResultSet) > 0){
			$Widgets = array();
			foreach($ResultSet as $Result){
				$Widgets[] = $Result;
			}
		}
		return $Widgets;
	}

	private function addStyles($El, $Styles) {
		if ($El->hasAttr('id') && $El->attr('id') != ''){
			//echo $El->attr('id') . '::<pre>';print_r($Styles);echo '</pre>';
			//return;
		}
		$css = array();
		foreach($Styles as $sInfo){
			if (substr($sInfo['definition_value'], 0, 1) == '{' || substr($sInfo['definition_value'], 0, 1) == '['){
				$css[$sInfo['definition_key']] = json_decode($sInfo['definition_value']);
			}
			else {
				$css[$sInfo['definition_key']] = $sInfo['definition_value'];
			}
			$El->css($sInfo['definition_key'], $css[$sInfo['definition_key']]);
		}
	}

	private function addInputs($El, $Config) {
		foreach($Config as $cInfo){
			if ($cInfo['configuration_key'] != 'id'){
				continue;
			}

			$El->attr('id', $cInfo['configuration_value']);
		}
	}

	private function processContainerChildren(&$El, $ChildArr) {
		foreach($ChildArr as $cInfo){
			$NewEl = htmlBase::newElement('div')
				->addClass('container');

			if (($cfgInfo = $this->getConfigInfo('container', $cInfo['container_id'])) !== false){
				$this->addInputs($NewEl, $cfgInfo);
			}

			if (($cssInfo = $this->getStyleInfo('container', $cInfo['container_id'])) !== false){
				$this->addStyles($NewEl, $cssInfo);
			}

			$El->append($NewEl);

			if (($Columns = $this->getContainerColumns($cInfo['container_id'])) !== false){
				$this->processContainerColumns($NewEl, $Columns);
			}

			if (($Children = $this->getContainerChildren($cInfo['container_id'])) !== false){
				$this->processContainerChildren($NewEl, $Children);
			}
		}
	}

	private function processContainerColumns(&$Container, $ColArr) {
		foreach($ColArr as $cInfo){
			$ColEl = htmlBase::newElement('div')
				->addClass('column');

			if (($cfgInfo = $this->getConfigInfo('column', $cInfo['column_id'])) !== false){
				$this->addInputs($ColEl, $cfgInfo);
			}

			if (($cssInfo = $this->getStyleInfo('column', $cInfo['column_id'])) !== false){
				$this->addStyles($ColEl, $cssInfo);
			}

			if (($Columns = $this->getColumnChildren($cInfo['column_id'])) !== false){
				$this->processContainerColumns($ColEl, $Columns);
			}

			$WidgetHtml = '';
			if (($Widgets = $this->getColumnWidgets($cInfo['column_id'])) !== false){
				foreach($Widgets as $wInfo){
					$WidgetEl = htmlBase::newElement('div')
						->addClass('widget')
						->setId('widget_' . $wInfo['widget_id']);

					$WidgetSettings = '';
					if (($cfgInfo = $this->getConfigInfo('widget', $wInfo['widget_id'])) !== false){
						$WidgetInputs = array();
						foreach($cfgInfo as $cfInfo){
							if ($cfInfo['configuration_key'] == 'widget_settings'){
								$WidgetSettings = json_decode(utf8_encode($cfInfo['configuration_value']));
							}else{
								$WidgetInputs[] = $cfInfo;
							}
						}

						if (!empty($WidgetInputs)){
							//$this->addInputs($WidgetEl, $WidgetInputs);
						}
					}

					if (($widgetCssInfo = $this->getStyleInfo('widget', $wInfo['widget_id'])) !== false){
						$this->addStyles($WidgetEl, $widgetCssInfo);
					}

					$WidgetClass = $this->getWidget($wInfo['identifier']);
					if ($WidgetClass !== false){
						if (isset($WidgetSettings->template_file) && !empty($WidgetSettings->template_file)){
							$WidgetClass->setBoxTemplateFile($WidgetSettings->template_file);
						}
						if (isset($WidgetSettings->id) && !empty($WidgetSettings->id)){
							$WidgetClass->setBoxId($WidgetSettings->id);
						}
						if (isset($WidgetSettings->widget_title) && !empty($WidgetSettings->widget_title)){
							$WidgetClass->setBoxHeading($WidgetSettings->widget_title->{Session::get('languages_id')});
						}
						if ($widgetCssInfo !== false){
							$WidgetClass->setWidgetCss($widgetCssInfo);
						}

						$WidgetClass->setWidgetProperties($WidgetSettings);

						$WidgetEl->html($WidgetClass->show($this));
						if ($WidgetEl->html() != ''){
							$WidgetHtml .= $WidgetEl->draw();
						}
					}
				}
			}
			$ColEl->html($WidgetHtml);

			$Container->append($ColEl);
		}
	}

	public function build(&$Construct) {
		$Profile = SES_Profiler::newProfile('templateLoad', true);
		foreach($this->getContainers() as $cInfo){
			$MainEl = htmlBase::newElement('div')
				->addClass('container');

			if ($cInfo['link_id'] > 0){
				$QlinkId = Doctrine_Manager::getInstance()
					->getCurrentConnection()
					->fetchAssoc('select container_id from template_manager_container_links where link_id = "' . $cInfo['link_id'] . '"');
				$containerId = $QlinkId[0]['container_id'];
			}
			else {
				$containerId = $cInfo['container_id'];
			}

			if (($cfgInfo = $this->getConfigInfo('container', $containerId)) !== false){
				$this->addInputs($MainEl, $cfgInfo);
			}

			if (($cssInfo = $this->getStyleInfo('container', $containerId)) !== false){
				$this->addStyles($MainEl, $cssInfo);
			}

			if (($Columns = $this->getContainerColumns($containerId)) !== false){
				$this->processContainerColumns($MainEl, $Columns);
			}

			if (($Children = $this->getContainerChildren($containerId)) !== false){
				$this->processContainerChildren($MainEl, $Children);
			}
			$Construct->append($MainEl);
		}
		$Profile->end();
	}
}