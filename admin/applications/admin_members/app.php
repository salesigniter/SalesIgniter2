<?php
$appContent = $App->getAppContentFile();

if (isset($_GET['gID'])){
	$App->setInfoBoxId($_GET['gID']);
}elseif (isset($_GET['action']) && $_GET['action'] == 'new_group'){
	$App->setInfoBoxId('new');
}
