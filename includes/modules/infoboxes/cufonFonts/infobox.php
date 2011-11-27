<?php
/*
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class InfoBoxCufonFonts extends InfoBoxAbstract {

	public function __construct(){
		global $App;
		$this->init('cufonFonts');
		$this->buildJavascriptMultiple = true;
	}

	public function show(){
			global $appExtension;
			$boxWidgetProperties = $this->getWidgetProperties();
			return $this->draw();
	}

	public function buildJavascript(){
		$boxWidgetProperties = $this->getWidgetProperties();

		$javascript = '/* Cufon --BEGIN-- */' . "\n" .

        '   $.getScript("'.sysConfig::getDirWsCatalog().'templates/'.Session::get('tplDir').'/fonts/'.$boxWidgetProperties->applied_font.'.js",function(){ '. "\n" .
		'   Cufon.replace("'.$boxWidgetProperties->applied_elements.'");' . "\n" .
		'   }); ' . "\n" .
		'/* Cufon --END-- */' . "\n";

		return $javascript;
	}
}
?>