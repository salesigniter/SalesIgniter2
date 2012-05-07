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
	'&showErrors' .
	(isset($_GET['noCache']) ? '&noCache' : '');

	$javascriptLink = sysConfig::getDirWsCatalog() . 'extensions/templateManager/catalog/globalFiles/javascript.php?' .
	'layout_id=' . $templateLayoutId .
	'&tplDir=' . sysConfig::get('TEMPLATE_DIRECTORY') .
	'&import=' . implode(',', $javascriptFiles) .
	'&showErrors' .
	(isset($_GET['noCache']) ? '&noCache' : '');

	global $currencies;
	$CurrencyInfo = $currencies->get(Session::get('currency'));
	?>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo sysLanguage::getCharset();?>" />
	<base href="<?php echo ((sysConfig::get('REQUEST_TYPE') == 'SSL') ? sysConfig::get('HTTPS_SERVER') : sysConfig::get('HTTP_SERVER')) . sysConfig::getDirWsCatalog(); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo $stylesheetLink;?>" />
	<script><?php require('includes/javascript/jsAppTop.php');?></script>
	<script type="text/javascript" src="<?php echo $javascriptLink;?>"></script>
	<script type="text/javascript">
		$(document).ready(function (){
			if ($.browser.msie === true){
				$('[style*="IE8_gradient"]').each(function (){
					var current = $(this).css('background-image');
					current = current.replace('height=100', 'height=' + $(this).outerHeight());
					//alert($(this).outerHeight() + "\n" + $(this).css('background-image') + "\n" + current);
					$(this).css('background-image', current);
				});
			}
		});
	</script>
<?php
	if (is_dir(sysConfig::get('DIR_FS_TEMPLATE') . 'fonts')){
		echo '<style>';
		$Dir = new DirectoryIterator(sysConfig::get('DIR_FS_TEMPLATE') . 'fonts');
		foreach($Dir as $fInfo){
			if ($fInfo->isDot() || $fInfo->isDir()){
				continue;
			}
			$FontInfo = new ttfInfo;
			$FontInfo->setFontFile($fInfo->getPathname());
			echo '@font-face {' . "\n" .
				'font-family: "' . $FontInfo->getFontFamily() . '";' . "\n" .
				'src: url("' . sysConfig::get('DIR_WS_TEMPLATE') . 'fonts/' . $fInfo->getBasename() . '") format("truetype");' . "\n" .
				'}' . "\n\n";
		}
		echo '</style>';
	}
?>
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
