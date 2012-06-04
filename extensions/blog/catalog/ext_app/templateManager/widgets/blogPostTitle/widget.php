<?php
class TemplateManagerWidgetBlogPostTitle extends TemplateManagerWidget {
	
	public function __construct(){
		$this->init('blogPostTitle', true, __DIR__);
		$this->enabled = true;
		$this->setBoxHeading(sysLanguage::get('INFOBOX_HEADING_BLOG_CATEGORIES'));

	}



	
	public function show(){
		
		if ($this->enabled === false) return;
		$htmlTitle = '';
		$this->setBoxContent($htmlTitle);
		
		return $this->draw();
	}
}
?>