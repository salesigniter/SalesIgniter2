<?php
$stylesheetLink = sysConfig::getDirWsCatalog() . 'extensions/templateManager/catalog/globalFiles/stylesheet.php?' .
	'env=admin' .
	'&' . Session::getSessionName() . '=' . Session::getSessionId() .
	'&tplDir=' . sysConfig::get('TEMPLATE_DIRECTORY') .
	'&import=' . implode(',', $App->getStylesheetFiles()) .
	(isset($_GET['noCache']) ? '&noCache' : '') .
	(isset($_GET['noMin']) ? '&noMin' : '');

$javascriptLink = sysConfig::getDirWsCatalog() . 'extensions/templateManager/catalog/globalFiles/javascript.php?' .
	'env=admin' .
	'&' . Session::getSessionName() . '=' . Session::getSessionId() .
	'&tplDir=' . sysConfig::get('TEMPLATE_DIRECTORY') .
	'&import=' . implode(',', $App->getJavascriptFiles()) .
	(isset($_GET['noCache']) ? '&noCache' : '') .
	(isset($_GET['noMin']) ? '&noMin' : '');

$CurrencyInfo = $currencies->get(Session::get('currency'));
?>
<!DOCTYPE html>
<html <?php echo sysLanguage::getHtmlParams(); ?>>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=<?php echo sysLanguage::getCharset(); ?>">
		<title><?php echo sprintf(sysLanguage::get('TITLE'), sysConfig::get('STORE_NAME')); ?></title>
		<base href="<?php echo ((sysConfig::get('REQUEST_TYPE') == 'SSL') ? sysConfig::get('HTTPS_SERVER') : sysConfig::get('HTTP_SERVER')) . sysConfig::get('DIR_WS_ADMIN'); ?>">
		<link rel="stylesheet" type="text/css" href="<?php echo $stylesheetLink;?>" />
		<script><?php require('includes/javascript/jsAppTop.php');?></script>
		<script type="text/javascript" src="<?php echo $javascriptLink;?>"></script>
<?php
if (isset($_GET['oError'])){
	echo '		<script type="text/javascript">alert(\'Onetime rentals has been disabled. If you would like to enable it, please contact www.itwebexperts.com\');</script>' . "\n";
}

$infoBoxId = $App->getInfoBoxId();

echo '		<script type="text/javascript">' . "\n" .
'			$(document).ready(function (){' . "\n";
if ($infoBoxId == 'new'){
	echo '				showInfoBox(\'new\');' . "\n";
}elseif ($infoBoxId != null){
	echo '				$(\'tbody > .ui-grid-row[infobox_id=' . $infoBoxId . ']\').click();' . "\n";
}else{
	echo '				if ($(\'tbody > .ui-grid-row:eq(0)\').attr(\'infobox_id\')){' . "\n" .
	'					$(\'tbody > .ui-grid-row:eq(0)\').click();' . "\n" .
	'				}' . "\n";
}
echo '			});' . "\n" .
'		</script>' . "\n";
?>
	</head>
	<body topmargin="0" leftmargin="0" bgcolor="#FFFFFF">
		<header><?php
			require(sysConfig::getDirFsAdmin() . 'includes/header.php');
		?></header>
		<table border="0" width="100%" cellspacing="0" cellpadding="15">
			<tr>
				<td width="100%" valign="top" class="main"><div id="bodyWrapprer" style="width:100%;position:relative;"><?php
					if ($messageStack->size('pageStack') > 0){
						echo $messageStack->output('pageStack', true) . '<br />';
					}

					if (isset($appContent) && file_exists(sysConfig::getDirFsAdmin() . 'applications/' . $appContent)){
						require(sysConfig::getDirFsAdmin() . 'applications/' . $appContent);
					}elseif (isset($appContent) && file_exists($appContent)){
						require($appContent);
					}else{
						require('template/content/' . $pageContent . '.tpl.php');
					}
				?></div></td>
			</tr>
		</table>
		<footer><?php
			require(sysConfig::getDirFsAdmin() . 'includes/footer.php');
		?></footer>
		<div class="sysMsgBlock" style="position:fixed;top:0px;left:0px;text-align:center;width:60%;margin-left:20%;margin-right:20%;display:none;">
		</div>
	</body>
	<div id="expiredSessionWindow" title="Session Has Expired" style="display:none;">
		<p>Your session has expired, please click ok to log back in.</p>
	</div>
</html>