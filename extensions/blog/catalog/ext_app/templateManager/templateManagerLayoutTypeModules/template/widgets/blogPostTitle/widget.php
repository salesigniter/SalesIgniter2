<?php
class TemplateManagerWidgetBlogPostTitle extends TemplateManagerWidget {
	
	public function __construct(){
		$this->init('blogPostTitle', true, __DIR__);
		$this->enabled = true;
		$this->setBoxHeading(sysLanguage::get('TEMPLATE_MANAGER_WIDGET_BLOGPOSTTITLE_TEXT_TITLE'));

	}



	
	public function show(TemplateManagerLayoutBuilder $LayoutBuilder){
		
		if ($this->enabled === false) return;
		$htmlTitle = '';
		$this->setBoxContent($htmlTitle);
		
		return $this->draw();
	}
}
?>