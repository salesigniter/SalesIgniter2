<?php
/**
 * Sales Igniter E-Commerce System
 * Version: {ses_version}
 *
 * I.T. Web Experts
 * http://www.itwebexperts.com
 *
 * Copyright (c) {ses_copyright} I.T. Web Experts
 *
 * This script and its source are not distributable without the written consent of I.T. Web Experts
 */

class TemplateManagerWidgetGoogleFonts extends TemplateManagerWidget {

	public function __construct(){
		global $App;
		$this->init('googleFonts');
		$this->buildJavascriptMultiple = true;
	}

	public function show(){
			global $appExtension;
			$boxWidgetProperties = $this->getWidgetProperties();
			return $this->draw();
	}

	public function buildJavascript(){
		$boxWidgetProperties = $this->getWidgetProperties();
		ob_start();
		?>
		var link = $("<link>");
		link.attr({
		type: 'text/css',
		rel: 'stylesheet',
		href: 'http://fonts.googleapis.com/css?family=<?php echo $boxWidgetProperties->applied_font;?>'
		});
			$("head").append( link );
		<?php
		$javascript = ob_get_contents();
		ob_end_clean();

		return $javascript;
	}
}
?>