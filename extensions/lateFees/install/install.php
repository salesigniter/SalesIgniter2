<?php
/*
	Late Fees Extension Version 1.0

	Sales Ingiter E-Commerce System v2
	I.T. Web Experts
	http://www.itwebexperts.com

	Copyright (c) 2011 I.T. Web Experts

	This script and it's source is not redistributable
*/

class lateFeesInstall extends extensionInstaller {
	
	public function __construct(){
		parent::__construct('lateFees');
	}

	public function install(){
		if (sysConfig::exists('EXTENSION_LATE_FEES_ENABLED') === true) return;
		
		parent::install();
	}
	
	public function uninstall($remove = false){
		if (sysConfig::exists('EXTENSION_LATE_FEES_ENABLED') === false) return;
		
		parent::uninstall($remove);
	}
}
?>