<?php
class InfoBoxPageButtons extends InfoBoxAbstract {
	
	public function __construct(){
		$this->init('pageButtons', __DIR__);
	}

	public function show(){
		global $Template;
		/* Page Content is the only widget that parses directly into its tpl file */
		$PageButtons = $Template->getVar('pageContent')->getVar('pageButtons');
		$this->setBoxContent($PageButtons);
		
		return $this->draw();
	}
}
?>