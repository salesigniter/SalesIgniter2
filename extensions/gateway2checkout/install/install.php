<?php
/*
	Gateway 2checkout Extension Version 1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2011 I.T. Web Experts

	This script and it's source is not redistributable
*/


class gateway2checkoutInstall extends extensionInstaller {

	public function __construct(){
		parent::__construct('gateway2checkout');
	}

	public function install(){
		if (defined('EXTENSION_GATEWAY2CHECKOUT_ENABLED')) return;

		parent::install();
	}

	public function uninstall($remove = false){
		if (!defined('EXTENSION_GATEWAY2CHECKOUT_ENABLED')) return;

		parent::uninstall($remove);
	}
}

?>
