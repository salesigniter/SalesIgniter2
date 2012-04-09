<!DOCTYPE html>
<html <?php echo sysLanguage::getHtmlParams();?>>
<head>
	<?php
	$title	= sysConfig::get('STORE_NAME');
	$desc	= sysConfig::get('STORE_NAME_ADDRESS');
	$keys	= sysConfig::get('STORE_NAME');

	EventManager::notify('PageLayoutHeaderTitle', &$title);
	EventManager::notify('PageLayoutHeaderMetaDescription', &$desc);
	EventManager::notify('PageLayoutHeaderMetaKeyword', &$keys);

	echo sprintf('		<title>%s</title>', $title) . "\n";
	echo sprintf('		<meta name="description" content="%s" />', $desc) . "\n";
	echo sprintf('		<meta name="keywords" content="%s" />', $keys) . "\n";

	$contents = EventManager::notifyWithReturn('PageLayoutHeaderCustomMeta');
	if (!empty($contents)){
foreach($contents as $html){
echo '		' . $html . "\n";
}
}

	$stylesheetLink = sysConfig::getDirWsCatalog() . 'extensions/templateManager/catalog/globalFiles/stylesheet.php?' .
	'layout_id=' . $templateLayoutId .
	'&tplDir=' . sysConfig::get('TEMPLATE_DIRECTORY') .
	'&import=' . implode(',', $stylesheets) .
	(isset($_GET['noCache']) ? '&noCache' : '');

	$javascriptLink = sysConfig::getDirWsCatalog() . 'extensions/templateManager/catalog/globalFiles/javascript.php?' .
	'layout_id=' . $templateLayoutId .
	'&tplDir=' . sysConfig::get('TEMPLATE_DIRECTORY') .
	'&import=' . implode(',', $javascriptFiles) .
	(isset($_GET['noCache']) ? '&noCache' : '');

	global $currencies;
	$CurrencyInfo = $currencies->get(Session::get('currency'));
	?>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo sysLanguage::getCharset();?>" />
	<base href="<?php echo ((sysConfig::get('REQUEST_TYPE') == 'SSL') ? sysConfig::get('HTTPS_SERVER') : sysConfig::get('HTTP_SERVER')) . sysConfig::getDirWsCatalog(); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo $stylesheetLink;?>" />
	<script type="text/javascript">
		var thisFile = '<?php echo basename($_SERVER['PHP_SELF']);?>';
		var SID = '<?php echo SID;?>';
		var sessionId = '<?php echo Session::getSessionId();?>';
		var sessionName = '<?php echo Session::getSessionName();?>';
	</script>
	<script type="text/javascript" src="<?php echo $javascriptLink;?>"></script>
	<script>
		jsCurrencies.setCode('<?php echo $CurrencyInfo['code'];?>');
		jsCurrencies.setTitle('<?php echo $CurrencyInfo['title'];?>');
		jsCurrencies.setSymbolLeft('<?php echo $CurrencyInfo['symbol_left'];?>');
		jsCurrencies.setSymbolRight('<?php echo $CurrencyInfo['symbol_right'];?>');
		jsCurrencies.setDecimalPoint('<?php echo $CurrencyInfo['decimal_point'];?>');
		jsCurrencies.setThousandsPoint('<?php echo $CurrencyInfo['thousands_point'];?>');
		jsCurrencies.setDecimalPlaces(<?php echo $CurrencyInfo['decimal_places'];?>);
		jsCurrencies.setValue(<?php echo $CurrencyInfo['value'];?>);
	</script>
</head>
<body>
<noscript>
	<div class="noscript">
		<div class="noscript-inner">
			<p>
				<strong>JavaScript seem to be disabled in your browser.</strong>
			</p>
			<p>
				You must have JavaScript enabled in your browser to utilize the functionality of this website.
			</p>
		</div>
	</div>
</noscript>
<?php
	if (sysConfig::get('DEMO_STORE') == 'on'){
?>
<p class="demo-notice">
This is a DEMO of Sales Igniter Rental Software. For more info <a href="http://www.rental-e-commerce-software.com" style="color:#ffffff;">Click Here</a>.
</p>
<?php
}
	
    echo $templateLayoutContent;
?>
</body>
</html>
