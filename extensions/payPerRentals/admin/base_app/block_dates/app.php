<?php
	$appContent = $App->getAppContentFile();

	if (isset($_GET['pID'])){
		$App->setInfoBoxId($_GET['pID']);
	} 
?>