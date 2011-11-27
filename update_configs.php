<?php
   require('includes/application_top.php');

function add_extra_fields($table, $column, $column_attr = 'VARCHAR(255) NULL'){

	$db=sysConfig::get('DB_DATABASE');
	$link = mysql_connect(sysConfig::get('DB_SERVER'), sysConfig::get('DB_SERVER_USERNAME'), sysConfig::get('DB_SERVER_PASSWORD'));
	if (! $link){
		die(mysql_error());
	}
	mysql_select_db($db , $link) or die("Select Error: ".mysql_error());

	$exists = false;
	$columns = mysql_query("show columns from $table");
	while($c = mysql_fetch_assoc($columns)){
		if($c['Field'] == $column){
			$exists = true;
			break;
		}
	}

	if(!$exists){
		mysql_query("ALTER TABLE `$table` ADD `$column`  $column_attr") or die("An error occured when running \n ALTER TABLE `$table` ADD `$column`  $column_attr \n" . mysql_error());
	}

}



add_extra_fields('admin','admin_override_password',"VARCHAR( 40 ) NOT NULL DEFAULT  ''");
add_extra_fields('admin','admins_stores'," text NOT NULL");
add_extra_fields('admin','admins_main_store',"int(11) NOT NULL");
add_extra_fields('admin','admin_simple_admin',"int(1) NOT NULL default '0'");

require('includes/application_bottom.php');
?>