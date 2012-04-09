<?php
if (isset($_GET['ui_state']) && $_GET['ui_state'] == 'dialog'){
echo $templateLayoutContent;
}else{
?>
<!DOCTYPE html>
<html <?php echo sysLanguage::getHtmlParams();?>>
<head>
	<?php
	$title	= sysConfig::get('STORE_NAME');

	EventManager::notify('PageLayoutHeaderTitle', &$title);

	echo sprintf('		<title>%s</title>', $title) . "\n";

	$stylesheetLink = sysConfig::getDirWsCatalog() . 'extensions/templateManager/catalog/globalFiles/stylesheet-mobile.php?' .
	'layout_id=' . $templateLayoutId .
	'&tplDir=' . sysConfig::get('TEMPLATE_DIRECTORY') .
	'&import=' . implode(',', $stylesheets) .
	($stylesheetCache === false || isset($_GET['noCache']) ? '&noCache' : '');

	$javascriptLink = sysConfig::getDirWsCatalog() . 'extensions/templateManager/catalog/globalFiles/javascript-mobile.php?' .
	'layout_id=' . $templateLayoutId .
	'&tplDir=' . sysConfig::get('TEMPLATE_DIRECTORY') .
	'&import=' . implode(',', $javascriptFiles) .
	($javascriptCache === false || isset($_GET['noCache']) ? '&noCache' : '');
	?>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo sysLanguage::getCharset();?>" />
	<base href="<?php echo ((sysConfig::get('REQUEST_TYPE') == 'SSL') ? sysConfig::get('HTTPS_SERVER') : sysConfig::get('HTTP_SERVER')) . sysConfig::getDirWsCatalog(); ?>" />
	<link rel="stylesheet" href="http://code.jquery.com/mobile/1.0.1/jquery.mobile-1.0.1.min.css" />
	<link rel="stylesheet" href="ext/jQuery/jQuery.mobile.css" />
	<script type="text/javascript">
		var thisFile = '<?php echo basename($_SERVER['PHP_SELF']);?>';
		var SID = '<?php echo SID;?>';
		var sessionId = '<?php echo Session::getSessionId();?>';
		var sessionName = '<?php echo Session::getSessionName();?>';
		var request_type = '<?php echo sysConfig::get('REQUEST_TYPE');?>';

		/*$('div').live('pagecreate', function(event, ui){
			var page = $(ui.prevPage);
			if (page.attr('data-cache') == 'never'){
				page.remove();
			}
		});*/
	</script>
	<script src="<?php echo $javascriptLink;?>"></script>
</head>
<?php
	$noCache = array(
		'shoppingCart',
		'createAccount',
		'checkout'
	);
?>
<body>
	<div data-role="page" style="background: url(/et_video/templates/moviestore/images/body_bg.png)"<?php echo (in_array($App->getPageName(), $noCache) ? ' data-cache="never"' : '');?>>
		<div data-role="header">
			<?php if (!isset($_GET['ui-state']) && !isset($_GET['ui_state'])){ ?>
			<a href="<?php echo itw_app_link(null, 'mobile', 'categories');?>">Categories</a>
			<?php } ?>
			<h1>ET Video Mobile</h1>
			<?php if (!isset($_GET['ui-state']) && !isset($_GET['ui_state'])){ ?>
			<a href="<?php echo itw_app_link(null, 'mobile', 'siteNav');?>">Site Nav</a>
			<?php } ?>
		</div>
		<div data-role="content">
			<?php
			if ($messageStack->size('pageStack') > 0){
				echo '<div class="ui-bar ui-bar-e"><h3>' .
		$messageStack->output('pageStack') .
		'</h3></div><br>';
			}
			?>
<?php if ($App->getAppName() == 'index' && $App->getPageName() == 'default'){ ?>
			<div class="ui-grid-b">
<?php
	global $storeProducts;
	$block = 'a';
	foreach($storeProducts->getNew(null, 25, 100, 100) as $pInfo){
			echo '<div class="ui-block-' . $block++ . '" style="text-align:center;">' .
			'<a href="' . itw_app_link('products_id=' . $pInfo['id'], 'mobile', 'productInfo') . '">' . $pInfo['image'] . '</a>' .
			'</div>';
			if ($block == 'd'){
		$block = 'a';
		}
			}
?>
			</div>
<?php }else{
		echo $templateLayoutContent;
		} ?>
		</div>
		<div data-role="footer" data-theme="etvideo-blue">
			<h4>&copy; 2012 ET Video</h4>
		</div>
	</div>
</body>
</html>
<?php
}
?>