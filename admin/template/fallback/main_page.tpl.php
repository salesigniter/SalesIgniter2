<?php
$stylesheetLink = sysConfig::getDirWsCatalog() . 'extensions/templateManager/catalog/globalFiles/stylesheet.php?' .
	'env=admin' .
	'&' . Session::getSessionName() . '=' . Session::getSessionId() .
	'&tplDir=' . sysConfig::get('TEMPLATE_DIRECTORY') .
	'&import[]=' . implode('&import[]=', $App->getStylesheetFiles()) .
	'&showErrors' .
	(isset($_GET['noCache']) ? '&noCache' : '') .
	(isset($_GET['noMin']) ? '&noMin' : '');

$javascriptLink = sysConfig::getDirWsCatalog() . 'extensions/templateManager/catalog/globalFiles/javascript.php?' .
	'env=admin' .
	'&' . Session::getSessionName() . '=' . Session::getSessionId() .
	'&tplDir=' . sysConfig::get('TEMPLATE_DIRECTORY') .
	'&import[]=' . implode('&import[]=', $App->getJavascriptFiles()) .
	'&showErrors' .
	(isset($_GET['noCache']) ? '&noCache' : '') .
	(isset($_GET['noMin']) ? '&noMin' : '');

$CurrencyInfo = $currencies->get(Session::get('currency'));

ob_start();
if (isset($appContent) && file_exists(sysConfig::getDirFsAdmin() . 'applications/' . $appContent)){
	require(sysConfig::getDirFsAdmin() . 'applications/' . $appContent);
}
elseif (isset($appContent) && file_exists($appContent)) {
	require($appContent);
}
else {
	require('template/content/' . $pageContent . '.tpl.php');
}
$BodyContent = ob_get_contents();
ob_end_clean();
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
	<script><?php
		echo $App->getAddedJavascript();
		?></script>
	<script>
		$(document).ready(function () {
			$(window).resize(function () {
				console.log($(window).width());
			});
			$('#mainNavMenu > li').each(function () {
				$(this)
					.addClass('ui-state-default')
					.mouseover(
					function () {
						$(this).addClass('ui-state-hover')
					}).mouseout(
					function () {
						$(this).removeClass('ui-state-hover');
					}).click(function (e) {
						$('#mainNavMenu > li.ui-state-active').removeClass('ui-state-active');
						$(this).addClass('ui-state-active');
						if ($(this).data('load_ajax') === true){
							$.ajax({
								url      : $(this).find('a').attr('href'),
								dataType : 'html',
								success  : function (data) {
									$('#landingPage').remove();
									var landingPage = $('#bodyWrapper').clone().attr('id', 'landingPage').html(data).show();
									$('#bodyWrapper').hide();
									landingPage.insertAfter($('#bodyWrapper'));
								}
							});
						}
						else {
							window.location = $(this).find('a').attr('href');
						}
					});
			});

			$('#mainNavMenu > li > a').click(function (e) {
				e.preventDefault();
			});

			$(document).on('click', '.removeLanding', function () {
				$(this).parent().remove();
				$('#bodyWrapper').show();
			});

			if ($('#appTips').size() > 0){
				$('#iconBar .ui-icon-help').click(
					function () {
						$('#appTips').dialog();
					}).show();
			}

			if ($.browser.msie === true && $.browser.version <= 8){
				$('[style*="IE8_gradient"], [class!=""]').each(function (){
					var current = $(this).css('background-image');
					if (/IE8_gradient/.test(current)){
						current = current.replace('height=100', 'height=' + $(this).outerHeight());
						//alert($(this).outerHeight() + "\n" + $(this).css('background-image') + "\n" + current);
						$(this).css('background-image', current);
					}
				});
			}
		});
	</script>
</head>
<body topmargin="0" leftmargin="0" bgcolor="#FFFFFF">
<?php
function makeLinkList($item) {
	$return = '<ul class="mainNavMenuChild">';
	foreach($item['children'] as $cInfo){
		$return .= '<li>';
		if (!empty($cInfo['children'])){
			$return .= $cInfo['text'];
			$return .= makeLinkList($cInfo);
		}
		else {
			$return .= '<a href="' . $cInfo['link'] . '">' . $cInfo['text'] . '</a>';
		}
		$return .= '</li>';
	}
	$return .= '</ul>';
	return $return;
}
if (Session::exists('login_id') === true){
	?>
<div id="logoBar">
	<img id="logo" src="<?php echo sysConfig::getDirWsAdmin();?>template/fallback/images/seslogo.png">
</div>
<div id="iconBar">
	<a href="javascript:void(0)" class="ui-icon ui-icon-help" tooltip="Click Here For Tips" style="display:none;"></a>&nbsp;&nbsp;
	<a href="<?php echo itw_app_link(null, 'index', 'default');?>" class="ui-icon ui-icon-home" tooltip="Home"></a>&nbsp;&nbsp;
	<a href="<?php echo itw_app_link(null, 'admin_account', 'default');?>" class="ui-icon ui-icon-myaccount" tooltip="My Account"></a>&nbsp;&nbsp;
	<a href="<?php echo itw_app_link('action=addToFavorites', 'index', 'default');?>" id="addToFavorites" class="ui-icon ui-icon-favorites-add" tooltip="Add To Favorites"></a>&nbsp;&nbsp;
	<a href="<?php echo itw_app_link('action=logoff', 'login', 'default');?>" class="ui-icon ui-icon-logoff" tooltip="Logoff"></a>&nbsp;&nbsp;
</div>
<div id="languageBar">
	<div id="languageBox">
		<?php
		$langDrop = htmlBase::newElement('selectbox')
			->setName('language')
			->selectOptionByValue(Session::get('languages_code'))
			->attr('onchange', 'this.form.submit()');
		foreach(sysLanguage::getLanguages() as $lInfo){
			$langDrop->addOption($lInfo['code'], $lInfo['name']);
		}
		echo '<form name="changeLanguage" action="' . itw_app_link(tep_get_all_get_params(array('app', 'appPage', 'action')), $App->getAppName(), $App->getAppPage()) . '" method="get">Language: ' . $langDrop->draw() . '</form>';
		?>
	</div>
</div>
<div id="openMenu">
	<button><span>Menu</span></button>
</div>
<div id="bodyContainer">
	<div id="leftColumn">
		<ul id="mainNavMenu">
			<li data-load_ajax="true">
				<span class="ui-icon ui-icon-required"></span><a href="<?php echo itw_app_link('action=landing&box=configuration', 'index', 'default');?>">Configuration</a>
			</li>
			<li data-load_ajax="true">
				<span class="ui-icon ui-icon-required"></span><a href="<?php echo itw_app_link('action=landing&box=accounting', 'index', 'default');?>">Accounting</a>
			</li>
			<li data-load_ajax="true">
				<span class="ui-icon ui-icon-calendar"></span><a href="<?php echo itw_app_link('action=landing&box=catalog', 'index', 'default');?>">Catalog</a>
			</li>
			<li data-load_ajax="true">
				<span class="ui-icon ui-icon-document"></span><a href="<?php echo itw_app_link('action=landing&box=cms', 'index', 'default');?>">Content Management</a>
			</li>
			<li data-load_ajax="true">
				<span class="ui-icon ui-icon-disk"></span><a href="<?php echo itw_app_link('action=landing&box=modules', 'index', 'default');?>">Modules</a>
			</li>
			<li data-load_ajax="true">
				<span class="ui-icon ui-icon-myaccount"></span><a href="<?php echo itw_app_link('action=landing&box=customers', 'index', 'default');?>">Customers</a>
			</li>
			<li data-load_ajax="true">
				<span class="ui-icon ui-icon-wrench"></span><a href="<?php echo itw_app_link('action=landing&box=tools', 'index', 'default');?>">Tools</a>
			</li>
			<li data-load_ajax="true">
				<span class="ui-icon ui-icon-print"></span><a href="<?php echo itw_app_link('action=landing&box=marketing', 'index', 'default');?>">Reports</a>
			</li>
			<li>
				<span class="ui-icon ui-icon-transferthick-e-w"></span><a href="<?php echo itw_app_link(null, 'data_manager', 'default');?>">Data Import/Export</a>
			</li>
		</ul>
	</div>
	<div id="rightColumn" class="ui-corner-all">
		<div class="pageHeading"><?php echo sysLanguage::get('PAGE_TITLE');?></div>
		<?php
		if ($messageStack->size('pageStack') > 0){
			echo '<br>' . $messageStack->output('pageStack') . '<br />';
		}
		?>
		<div id="bodyWrapper"><?php echo $BodyContent; ?></div>
		<br>

		<div class="sysMsgBlock" style="position:fixed;top:0px;left:0px;text-align:center;width:60%;margin-left:20%;margin-right:20%;display:none;"></div>
	</div>
</div>
<footer><?php
	require(sysConfig::getDirFsAdmin() . 'includes/footer.php');
	?></footer>
	<?php
}
else {
	echo $BodyContent;
}
?>
</body>
<div id="expiredSessionWindow" title="Session Has Expired" style="display:none;">
	<p>Your session has expired, please click ok to log back in.</p>
</div>
</html>