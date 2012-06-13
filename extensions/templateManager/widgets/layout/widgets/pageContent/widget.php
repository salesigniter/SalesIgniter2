<?php
class TemplateManagerWidgetPageContent extends TemplateManagerWidget {
	
	public function __construct(){
		$this->init('pageContent', __DIR__);
	}

	public function show(){
		global $App, $Template;
		/* @TODO: Make This Work
		$templateDir = sysConfig::get('DIR_FS_TEMPLATE');

		$pageContent = new Template('pageContent.tpl', $templateDir);

		$checkFiles = array(
			$templateDir . '/applications/' . $App->getAppName() . '/' . $App->getPageName() . '.php',
			sysConfig::getDirFsCatalog() . '/applications/' . $App->getAppName() . '/pages/' . $App->getPageName() . '.php'
		);

		$requireFile = false;
		foreach($checkFiles as $filePath){
			if (file_exists($filePath)){
				$requireFile = $filePath;
				break;
			}
		}

		if ($requireFile !== false){
			require($requireFile);

			foreach($pageContent->getVars() as $k => $v){
				$this->setTemplateVar($k, $v);
			}
		}
*/
		/* Page Content is the only widget that parses directly into its tpl file */
		$PageContent = $Template->getVar('pageContent');
		$this->setBoxContent($PageContent->getVar('pageContent'));
		
		return $this->draw();
	}
}
?>