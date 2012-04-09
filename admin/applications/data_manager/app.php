<?php
	require(sysConfig::getDirFsAdmin() . 'includes/classes/upload.php');

	$appContent = $App->getAppContentFile();


	$separator = "\t";
	$default_image_manufacturer = '';
	$default_image_product = '';
	$default_image_category = '';
	$active = 'Active';
	$inactive = 'Inactive';
	$deleteStatus = 'Delete';
	$zero_qty_inactive = false;
	$replace_quotes = false;
	
	$showLogInfo = false;

	/* SHOULD NOT BE HERE, IT IS IN THE GENERAL REMOVED AND SHOULD BE MOVED BACK THERE IF YOU NEED IT */
	function tep_get_tax_class_rate($tax_class_id) {
		$tax_multiplier = 0;
		$QtaxRate = Doctrine_Manager::getInstance()
			->getCurrentConnection()
			->fetchAssoc("select SUM(tax_rate) as tax_rate from tax_rates WHERE  tax_class_id = '" . $tax_class_id . "' GROUP BY tax_priority");
		if (sizeof($QtaxRate)) {
			foreach ($QtaxRate as $tax) {
				$tax_multiplier += $tax['tax_rate'];
			}
		}
		return $tax_multiplier;
	}

	function tep_get_tax_title_class_id($tax_class_title) {
		$QtaxClass = Doctrine_Manager::getInstance()
			->getCurrentConnection()
			->fetchAssoc("select tax_class_id from tax_class WHERE tax_class_title = '" . $tax_class_title . "'" );
		$tax_class_id = $QtaxClass[0]['tax_class_id'];
		return $tax_class_id ;
	}

//if (isset($_POST['buttoninsert'])) $action = 'importProducts';
//if (isset($_POST['buttonsplit'])) $action = 'splitFile';
//if (isset($_POST['buttoninserttemp'])) $action = 'importProducts';
?>