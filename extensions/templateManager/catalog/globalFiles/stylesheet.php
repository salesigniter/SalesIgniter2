<?php
chdir('../../../../');
if (isset($_GET['layout_id'])){
	$env = 'catalog';
	$layoutId = $_GET['layout_id'];
	$templateDir = isset($_GET['tplDir']) ? $_GET['tplDir'] : '';
}
else {
	$env = 'admin';
	$layoutId = '9999';
	$templateDir = 'administration';
}
$import = 'noimport';
if (isset($_GET['import']) && !empty($_GET['import'])){
	$import = $_GET['import'];
}
$cacheKey = $templateDir . '-' . md5($_SERVER['HTTP_USER_AGENT'] . '-' . $layoutId . '-' . $import);
$noCache = isset($_GET['noCache']);
$noMin = isset($_GET['noMin']);

require('includes/classes/system_cache.php');
$StylesheetCache = new SystemCache($cacheKey, 'cache/' . $env . '/stylesheet/');
if ($noCache === false && $StylesheetCache->loadData() === true){
	$StylesheetCache->output(false, true);
	exit;
}
else {
	include('includes/application_top.php');

	$sources = array();
	$sources[] = sysConfig::getDirFsCatalog() . 'ext/jQuery/themes/smoothness/ui.core.css';
	$sources[] = sysConfig::getDirFsCatalog() . 'ext/jQuery/themes/smoothness/ui.theme.css';

	ob_start();

	foreach($sources as $filePath){
		if (file_exists($filePath)){
			echo '/*' . "\n" .
				' * Required File' . "\n" .
				' * Path: ' . $filePath . "\n" .
				' * --BEGIN--' . "\n" .
				' */' . "\n";
			require($filePath);
			echo '/*' . "\n" .
				' * Required File' . "\n" .
				' * Path: ' . $filePath . "\n" .
				' * --END--' . "\n" .
				' */' . "\n";
		}
	}

	/* Overwrites for the core css framework --BEGIN-- */
	?>
body, div, td { font-family: Verdana, Arial, sans-serif;font-size: 12px; }
div {  }
a { text-decoration: none;color: #626262; }
a:hover { text-decoration: underline;color: #626262; }
form { display: inline; }
textarea { width: 100%;font-size: 11px; }

h1 { font-size: inherit;color: inherit;font-weight: inherit; }
h2 { font-size: inherit;color: inherit;font-weight: inherit; }
h3 { font-size: inherit;color: inherit;font-weight: inherit; }
h4 { font-size: inherit;color: inherit;font-weight: inherit; }

.inputRequirement { color: red;}
.main, .main { font-size: .9em;line-height: 1.5em; }
.smallText { font-family: Arial, sans-serif;font-size: .9em; }
.column { margin:0; }
/*.widget { min-height:32px; }*/
<?php
	/* Overwrites for the core css framework --END-- */

	$sources = array(
		sysConfig::getDirFsCatalog() . 'ext/jQuery/themes/smoothness/ui.accordion.css',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/themes/smoothness/ui.datepicker.css',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/themes/smoothness/ui.dialog.css',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/themes/smoothness/ui.progressbar.css',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/themes/smoothness/ui.resizable.css',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/themes/smoothness/ui.slider.css',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/themes/smoothness/ui.tabs.css',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/themes/smoothness/ui.tooltip.css',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/themes/smoothness/ui.autocomplete.css',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/themes/smoothness/ui.button.css',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/themes/smoothness/ui.stars.css',
		sysConfig::getDirFsCatalog() . 'ext/jQuery/themes/smoothness/ui.menu.css'
	);

	if ($App->getEnv() == 'admin'){
		$sources[] = sysConfig::getDirFsCatalog() . 'ext/jQuery/external/filemanager/jquery.filemanager.css';
		$sources[] = sysConfig::getDirFsCatalog() . 'ext/jQuery/external/fileuploader/jquery.fileuploader.css';
	}

	foreach($sources as $filePath){
		if (file_exists($filePath)){
			echo '/*' . "\n" .
				' * Required File' . "\n" .
				' * Path: ' . $filePath . "\n" .
				' * --BEGIN--' . "\n" .
				' */' . "\n";
			require($filePath);
			echo '/*' . "\n" .
				' * Required File' . "\n" .
				' * Path: ' . $filePath . "\n" .
				' * --END--' . "\n" .
				' */' . "\n";
		}
	}
	/* Overwrites for jQuery UI Settings --BEGIN-- */
	?>
/*
* Border Radius
* top-left top-right bottom-right bottom-left
*/
.ui-corner-all { <?php echo buildBorderRadius('4px', '4px', '4px', '4px'); ?> }
.ui-corner-all-big { <?php echo buildBorderRadius('10px', '10px', '10px', '10px'); ?> }
.ui-corner-all-small { <?php echo buildBorderRadius('2px', '2px', '2px', '2px'); ?> }
.ui-corner-all-medium { <?php echo buildBorderRadius('6px', '6px', '6px', '6px'); ?> }
.ui-corner-all-large { <?php echo buildBorderRadius('10px', '10px', '10px', '10px'); ?> }
.ui-corner-all-xlarge { <?php echo buildBorderRadius('14px', '14px', '14px', '14px'); ?> }
.ui-corner-tl { <?php echo buildBorderRadius('4px', '0px', '0px', '0px'); ?> }
.ui-corner-tr { <?php echo buildBorderRadius('0px', '4px', '0px', '0px'); ?> }
.ui-corner-br { <?php echo buildBorderRadius('0px', '0px', '4px', '0px'); ?> }
.ui-corner-bl { <?php echo buildBorderRadius('0px', '0px', '0px', '4px'); ?> }
.ui-corner-top { <?php echo buildBorderRadius('4px', '4px', '0px', '0px'); ?> }
.ui-corner-top-big { <?php echo buildBorderRadius('10px', '10px', '0px', '0px'); ?> }
.ui-corner-bottom { <?php echo buildBorderRadius('0px', '0px', '4px', '4px'); ?> }
.ui-corner-bottom-big { <?php echo buildBorderRadius('0px', '0px', '10px', '10px'); ?> }
.ui-corner-right { <?php echo buildBorderRadius('0px', '4px', '4px', '0px'); ?> }
.ui-corner-left { <?php echo buildBorderRadius('4px', '0px', '0px', '4px'); ?> }

.ui-button {  }
.ui-button.ui-state-default { color:#555555;border-color: #d3d3d3;<?php echo buildLinearGradient(270, array(array('#ededed', 0), array('#ededed', .5), array('#e6e6e6', .5), array('#eeeeee', 1))); ?> }
.ui-button.ui-state-hover { color:#212121;border-color: #999999;<?php echo buildLinearGradient(270, array(array('#e4e4e4', 0), array('#e4e4e4', .5), array('#dadada', .5), array('#e7e7e7', 1))); ?> }
.ui-button.ui-state-active { color:#212121;border-color: #aaaaaa;background:#ffffff; }
.ui-button .ui-button-icon-primary, .ui-button-text-icon .ui-button-icon-primary, .ui-button-text-icons .ui-button-icon-primary, .ui-button-icons-only .ui-button-icon-primary{ left:0.3em; }

.ui-widget-header { border-color: #cccccc;color:#222222;line-height: 1.35em;vertical-align: top; }
.ui-widget-content { background: #ffffff; color: #222222; }
.ui-widget-content a { color: #333333; }
.ui-widget-header .ui-icon { background-image: url(<?php echo jqueryIconsPath('ffffff'); ?>); }
.ui-widget-footer-box { margin-top:.5em; }
.ui-widget-footer-box .ui-button { margin:.5em; }

<?php
	/* Overwrites for jQuery UI Settinge --END-- */

	/* Our core managed css --BEGIN-- */
	?>
.errorReport { margin:.5em;padding: 0.7em;border:none;border: 1px solid #000000; }
.errorReport .ui-icon { float: left; margin-right: 0.3em; }
.errorReport .ui-state-error { border-color:#fdcfcf;<?php echo buildSimpleGradient('#fea4a4', '#fc7373'); ?> }
.errorReport .ui-state-warning { border-color:#fceede;<?php echo buildSimpleGradient('#fedfbd', '#fbb86f'); ?> }
.errorReport .ui-state-notice { border-color:#cfddf7;<?php echo buildSimpleGradient('#a4c4fe', '#6499fa'); ?> }

.pageStackContainer { font-family: Verdana, Arial, sans-serif; font-size: .9em; }
.pageStackContainer .ui-widget { margin-bottom: 1em; }

.ui-infobox { background: #dcdcdc;position: relative;margin-bottom: 10px; }
.ui-infobox-header { color: #ffffff;font-weight: bold;font-size: 1em;position:relative;margin:0; padding:0;line-height:1em;}
.ui-infobox-header-text { font-weight:normal;margin:0;margin-left:.5em;padding:0;color:#ffffff;;vertical-align:middle }
.ui-infobox-header-link { float:right;vertical-align:middle;margin-right:.5em; }
.ui-infobox-header-link a { vertical-align:middle; }
.ui-infobox-content { margin: .5em; }
.ui-infobox-header .ui-icon { position:relative; display:inline-block; }
.ui-infobox-content .ui-icon-triangle-1-e { display: inline-block; }

.ui-infobox-header .ui-icon { position:relative; display:inline-block;background-image: url(<?php echo $jqueryThemeIcons; ?>/ui-icons_ffffff_256x240.png); }
.ui-infobox-content .ui-icon-triangle-1-e { display: inline-block; }

.ui-ajax-loader { display: block;text-indent: -99999px;overflow: hidden;background-repeat: no-repeat; }
.ui-ajax-loader-xsmall { width: 10px;height: 10px;background-image: url(<?php echo $jqueryThemeIcons; ?>/ajax_loader_xsmall.gif); }
.ui-ajax-loader-icon { width: 16px;height: 16px;background-image: url(<?php echo $jqueryThemeIcons; ?>/ajax_loader_icon.gif); }
.ui-ajax-loader-small { opacity: 2; width: 20px; height: 20px; background-image: url(<?php echo $jqueryThemeIcons; ?>/ajax_loader_small.gif); }
.ui-ajax-loader-normal { width: 40px; height: 40px; background-image: url(<?php echo $jqueryThemeIcons; ?>/ajax_loader_normal.gif); }
.ui-ajax-loader-large { width: 60px; height: 60px; background-image: url(<?php echo $jqueryThemeIcons; ?>/ajax_loader_large.gif); }
.ui-ajax-loader-xlarge { width: 80px; height: 80px; background-image: url(<?php echo $jqueryThemeIcons; ?>/ajax_loader_xlarge.gif); }

.ui-ajax-loader-back{ background-image: url(<?php echo $templateDir; ?>images/bg_ajax.png);width:375px;height:200px; }
.ui-ajax-loader-dialog{ margin-left:150px; margin-top:60px; }

.moduleRow.ui-state-default, .moduleRow.ui-state-hover, .moduleRow.ui-state-active { color:#ffffff; }
.moduleRow.ui-state-default { border: 1px solid transparent; }
.moduleRow.ui-state-hover { border-color:#background: #cccccc; }
.moduleRow.ui-state-active { border-color:#d71a14;background-color:#ffffff; }

.ui-contentbox { width: 100%;position: relative;margin-bottom: 0px; }
.ui-contentbox-header { font-weight: bold;font-size: 1em; }
.ui-contentbox-header-text { font-family:Arial; font-size:18px; color:#000000;background-image:url(<?php echo $templateDir; ?>images/icon_widget2.png);background-repeat:no-repeat;padding-right:15px;margin-left:10px;height:39px; line-height:33px; background-position:right center;}
.ui-contentbox-content { position:relative;background-color:#fcf9f9; border: 1px solid #e2e0de; padding-left:10px; }
.ui-contentbox-content h1{ margin:0;margin-bottom:40px;color:#ffffff; }
.ui-contentbox-content h3{ margin:0; }
.ui-contentbox .ui-widget-content { border:none; }
.ui-contentbox .moreInfo { margin-left:.5em; }

.productListing-heading { height: 34px;font-weight: normal;font-family: Arial;font-size: 14px;color: #ffffff;background: url(<?php echo $templateDir; ?>images/infobox_header.png) repeat-x top left; }
a.productListing-heading, a.productListing-heading:hover { color: #ffffff;background: none;height: 15px; }
.productListingColBoxContainer{text-align:center;margin:.5em; width:110px;float:left;margin-left:5px;margin-top:15px;background-color:#f2f2f2;border:1px solid #cccccc;padding-bottom:4px; }

.productListingColBoxContent_image{ padding-top:4px; }
.productListingColBoxInner {min-height: 210px;}
.productListingColBoxTitle {height: 60px; display: table-cell; text-align: center; vertical-align:middle }
.productListingColContents { }
.productListingColPager { color:#ffffff;background: #313131;font-size: .8em;position:relative; padding: .5em;}
.productListingColPager a { color:#ffffff; }
.productListingColPagerLink { font-size:1em;padding: .4em .6em;background-color:#cccccc; }
a.productListingColPagerLink:hover { text-decoration:none; }
.productListingColPagerLinkActive { font-size:1em;padding: .4em .6em;font-weight: bold; }

.productListingRowContainer { padding: .5em; }
.productListingRowContents { }
.productListingRow-even { background: #e8e8e8; }
.productListingRow-odd { background: transparent; }
.productListingRowPager { color:#ffffff;background: #313131;font-size: .8em;position:relative; }
.productListingRowPager a { color:#ffffff; }
.productListingRowPagerLink { font-size:1em;padding: .4em .6em;background-color:#cccccc; }
a.productListingRowPagerLink:hover { text-decoration:none; }
.productListingRowPagerLinkActive { font-size:1em;padding: .4em .6em;font-weight: bold; }

.pageHeaderContainer { line-height: 2em;vertical-align:middle;margin:.2em; }
.pageHeaderContainer .pageHeaderText { vertical-align:middle; }
.pageHeaderContainer .ui-icon { margin-left:.3em;display:inline-block;vertical-align:middle; }
.pageContent { margin:.2em; }
.pageButtonBar { text-align:right; padding:.3em; margin-top:.5em; }
.ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset { font-size: 0.9em; }
.ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset button { padding: .1em; }
a.readMore, a.readMore:link, a.readMore:visited, a.readMore:active {color: #c40000; text-decoration: underline; font-weight: bold; }
a.readMore:hover{color: black;}
<?php
	/*
		 * @TODO: Move to pay per rentals infobox buildStylesheet function
		 */
	if ($appExtension->isInstalled('payPerRentals')){
		?>
	.ui-datepicker-reserved { background: #FF0000; }
	.ui-datepicker-reserved span.ui-state-default { background: #FF0000; }

	<?php
	}

	/*
		 * @TODO: Move to blog infobox buildStylesheet function
		 */
	if ($appExtension->isInstalled('blog')){
		?>
	#blogcategoriesModuleMenu h3{ margin:0;padding:0; }
	#blogcategoriesModuleMenu ul{ list-style-type:none; }
	#blogcategoriesModuleMenu ul li{ margin:0;padding:0;background:transparent; }
	#blogarchivesModuleMenu ul{ list-style-type:none; margin:0; padding:0; }
	#blogarchivesModuleMenu ul li{ margin:0;padding:0;background:transparent; }
	.blogInfoboxLink{ border-color:transparent; }
	.blogInfoboxLink:hover{ text-decoration:none !important; }
	.comf, .captcha_img{ display:block;margin-bottom:10px; }
	#cke_comment_text{ width:80%; }
	<?php
	}
	/* Our core managed css --END-- */

	if (isset($_GET['import']) && !empty($_GET['import'])){
		foreach(explode(',', $_GET['import']) as $filePath){
			if (substr($filePath, -4) != '.css'){
				continue;
			}

			$requireFile = false;
			if (file_exists($filePath)){
				$requireFile = $filePath;
			}
			elseif (file_exists(sysConfig::get('DIR_FS_DOCUMENT_ROOT') . $filePath)) {
				$requireFile = sysConfig::get('DIR_FS_DOCUMENT_ROOT') . $filePath;
			}
			elseif (file_exists(sysConfig::getDirFsCatalog() . $filePath)) {
				$requireFile = sysConfig::getDirFsCatalog() . $filePath;
			}
			elseif (file_exists(sysConfig::getDirFsAdmin() . $filePath)) {
				$requireFile = sysConfig::getDirFsAdmin() . $filePath;
			}

			if ($requireFile !== false){
				echo '/*' . "\n" .
					' * Imported File' . "\n" .
					' * Path: ' . $requireFile . "\n" .
					' * --BEGIN--' . "\n" .
					' */' . "\n";
				require($requireFile);
				echo '/*' . "\n" .
					' * Imported File' . "\n" .
					' * Path: ' . $requireFile . "\n" .
					' * --END--' . "\n" .
					' */' . "\n";
			}
		}
	}

	if ($env == 'catalog'){
		$TemplateManager = $appExtension->getExtension('templateManager');
		$TemplateManager->loadWidgets($templateDir);
		$boxStylesEntered = array();
		$infoBoxSources = array();
		$boxStylesheetSourcesEntered = array();
		$addCss = '';

		function getElementId($dataArr) {
			if (isset($dataArr['widget_id'])){
				$idCol = 'widget_id';
				$idVal = $dataArr['widget_id'];
				$configTable = 'template_manager_layouts_widgets_configuration';
			}
			elseif (isset($dataArr['column_id'])) {
				$idCol = 'column_id';
				$idVal = $dataArr['column_id'];
				$configTable = 'template_manager_layouts_columns_configuration';
			}
			elseif (isset($dataArr['container_id'])) {
				$idCol = 'container_id';
				$idVal = $dataArr['container_id'];
				$configTable = 'template_manager_layouts_containers_configuration';
			}

			if (!isset($idCol)){
				print_r($dataArr);
			}
			$QconfigId = Doctrine_Manager::getInstance()
				->getCurrentConnection()
				->fetchAssoc('select configuration_value from ' . $configTable . ' where configuration_key = "id" and ' . $idCol . ' = "' . $idVal . '"');
			return $QconfigId[0]['configuration_value'];
		}

		function parseContainer($Container) {
			global $TemplateManager, $boxStylesEntered, $infoBoxSources, $boxStylesheetSourcesEntered, $addCss;

			if (isset($Container['widget_id'])){
				$typeId = $Container['widget_id'];
				$type = 'widget';
			}
			elseif (isset($Container['column_id'])) {
				$typeId = $Container['column_id'];
				$type = 'column';
			}
			elseif (isset($Container['container_id'])) {
				$typeId = $Container['container_id'];
				$type = 'container';
			}

			if (($ElementId = getElementId($Container)) != ''){
				if (($Styles = $TemplateManager->getStyleInfo($type, $typeId)) !== false){
					$Style = new StyleBuilder();
					$Style->setSelector('#' . $ElementId);
					foreach($Styles as $sInfo){
						$Style->addRule($sInfo['definition_key'], $sInfo['definition_value']);
					}
					$addCss .= $Style->outputCss();
				}
			}

			if ($type == 'container' && (($Containers = $TemplateManager->getContainerChildren($typeId)) !== false)){
				foreach($Containers as $ChildObj){
					parseContainer($ChildObj);
				}
			}
			elseif ($type == 'container' && (($Columns = $TemplateManager->getContainerColumns($typeId)) !== false)) {
				foreach($Columns as $ChildObj){
					parseContainer($ChildObj);
				}
			}
			elseif ($type == 'column' && (($Columns = $TemplateManager->getColumnChildren($typeId)) !== false)) {
				foreach($Columns as $ChildObj){
					parseContainer($ChildObj);
				}
			}
			elseif ($type == 'column' && (($Widgets = $TemplateManager->getColumnWidgets($typeId)) !== false)) {
				foreach($Widgets as $wInfo){
					parseContainer($wInfo);
				}
			}
			elseif ($type == 'widget') {
				if (($Configuration = $TemplateManager->getConfigInfo($type, $typeId)) !== false){
					foreach($Configuration as $config){
						if ($config['configuration_key'] == 'widget_settings'){
							$WidgetSettings = json_decode($config['configuration_value']);
							break;
						}
					}

					if (($Styles = $TemplateManager->getStyleInfo($type, $typeId)) !== false){
						$Style = new StyleBuilder();
						$Style->setSelector('#widget_' . $typeId);
						foreach($Styles as $sInfo){
							$Style->addRule($sInfo['definition_key'], $sInfo['definition_value']);
						}
						$addCss .= $Style->outputCss();
					}

					$WidgetClass = $TemplateManager->getWidget($Container['identifier']);
					if ($WidgetClass !== false){
						if (isset($WidgetSettings->id) && !empty($WidgetSettings->id)){
							$WidgetClass->setBoxId($WidgetSettings->id);
						}
						$WidgetClass->setWidgetProperties($WidgetSettings);
						if (method_exists($WidgetClass, 'buildStylesheet')){
							if ($WidgetClass->buildStylesheetMultiple === true || !in_array($WidgetClass->getBoxCode(), $boxStylesEntered)){
								echo $WidgetClass->buildStylesheet();

								$boxStylesEntered[] = $WidgetClass->getBoxCode();
							}
						}
						if (method_exists($WidgetClass, 'getStylesheetSources')){
							if (!in_array($WidgetClass->getBoxCode(), $boxStylesheetSourcesEntered)){
								$infoBoxCssFiles = $WidgetClass->getStylesheetSources();
								foreach($infoBoxCssFiles as $infoBoxCssFile){
									if (file_exists($infoBoxCssFile)){
										$infoBoxSources[] = $infoBoxCssFile;
									}
								}

								$boxStylesheetSourcesEntered[] = $WidgetClass->getBoxCode();
							}
						}
					}
				}
			}
		}

		$Layout = Doctrine_Manager::getInstance()
			->getCurrentConnection()
			->fetchAssoc('select * from template_manager_layouts where layout_id = "' . (int)$_GET['layout_id'] . '"');
		if ($Layout){
			if (($LayoutStyles = $TemplateManager->getStyleInfo('layout', $Layout[0]['layout_id'])) !== false){
				$StyleBuilder = new StyleBuilder();
				$StyleBuilder->setSelector('body');
				$rules = array();
				foreach($LayoutStyles as $sInfo){
					$StyleBuilder->addRule($sInfo['definition_key'], $sInfo['definition_value']);
				}
				$addCss .= $StyleBuilder->outputCss();
			}

			$Containers = Doctrine_Manager::getInstance()
				->getCurrentConnection()
				->fetchAssoc('select * from template_manager_layouts_containers where layout_id = "' . $Layout[0]['layout_id'] . '" and parent_id = 0 order by sort_order');
			if (sizeof($Containers) > 0){
				foreach($Containers as $cInfo){
					if ($cInfo['link_id'] > 0){
						$Link = Doctrine_Manager::getInstance()
							->getCurrentConnection()
							->fetchAssoc('select c.* from template_manager_container_links l left join template_manager_layouts_containers c using(container_id) where l.link_id = "' . $cInfo['link_id'] . '"');
						parseContainer($Link[0]);
					}
					else {
						parseContainer($cInfo);
					}
				}
			}

			foreach($infoBoxSources as $filePath){
				if (file_exists($filePath)){
					echo '/*' . "\n" .
						' * Template Widget Required File' . "\n" .
						' * Path: ' . $filePath . "\n" .
						' * --BEGIN--' . "\n" .
						' */' . "\n";
					require($filePath);
					echo '/*' . "\n" .
						' * Template Widget Required File' . "\n" .
						' * Path: ' . $filePath . "\n" .
						' * --END--' . "\n" .
						' */' . "\n";
				}
			}

			echo '/*' . "\n" .
				' * Layout Manager Generated Styles' . "\n" .
				' * --BEGIN--' . "\n" .
				' */' . "\n";
			echo $addCss;
			echo '/*' . "\n" .
				' * Layout Manager Generated Styles' . "\n" .
				' * --END--' . "\n" .
				' */' . "\n";
		}

		echo '/*' . "\n" .
			' * Template Stylesheet' . "\n" .
			' * Path: ' . sysConfig::get('DIR_FS_TEMPLATE') . 'stylesheet.css' . "\n" .
			' * --BEGIN--' . "\n" .
			' */' . "\n";
		require(sysConfig::get('DIR_FS_TEMPLATE') . 'stylesheet.css');
		echo '/*' . "\n" .
			' * Template Stylesheet' . "\n" .
			' * Path: ' . sysConfig::get('DIR_FS_TEMPLATE') . 'stylesheet.css' . "\n" .
			' * --END--' . "\n" .
			' */' . "\n";
	}
	else {
		echo '/*' . "\n" .
			' * Template Stylesheet' . "\n" .
			' * Path: ' . sysConfig::get('DIR_FS_TEMPLATE') . 'stylesheet.css' . "\n" .
			' * --BEGIN--' . "\n" .
			' */' . "\n";
		require(sysConfig::get('DIR_FS_TEMPLATE') . 'stylesheet.css');
		echo '/*' . "\n" .
			' * Template Stylesheet' . "\n" .
			' * Path: ' . sysConfig::get('DIR_FS_TEMPLATE') . 'stylesheet.css' . "\n" .
			' * --END--' . "\n" .
			' */' . "\n";
	}

	$fileContent = ob_get_contents();
	ob_end_clean();

	function src1_fetch() {
		global $fileContent;
		return $fileContent;
	}

	$nowTime = time();
	$maxAge = (60 * 60 * 24 * 2);
	$expiresTime = $nowTime + $maxAge;

	if ($noMin === true || sysConfig::get('TEMPLATE_STYLESHEET_COMPRESSION') == 'none'){
		$Result = array(
			'headers' => array(
				'Content-Type'	 => 'text/css'
			),
			'content' => src1_fetch()
		);
		if ($noCache === false && sysConfig::get('TEMPLATE_STYLESHEET_CACHE') == 1){
			$Result['headers']['Expires'] = gmdate('D, d M Y H:i:s \G\M\T', $expiresTime);
			$Result['headers']['Last-Modified'] = gmdate('D, d M Y H:i:s \G\M\T', $nowTime);
			$Result['headers']['Cache-Control'] = 'max-age=' . $maxAge;
		}
		else {
			$Result['headers']['Expires'] = 'Sat, 26 Jul 1997 05:00:00 GMT';
			$Result['headers']['Last-Modified'] = gmdate('D, d M Y H:i:s \G\M\T', $nowTime);
			$Result['headers']['Cache-Control'] = 'no-cache, must-revalidate';
		}
	}
	else {
		define('MINIFY_MIN_DIR', sysConfig::getDirFsCatalog() . 'min');

		/*
		 * This script implements a Minify server for a single set of sources.
		 * If you don't want '.php' in the URL, use mod_rewrite...
		 */

		// setup Minify
		set_include_path(MINIFY_MIN_DIR . '/lib' . PATH_SEPARATOR . get_include_path());
		require 'Minify.php';
		require 'Minify/Cache/File.php';
		Minify::setCache(new Minify_Cache_File()); // guesses a temp directory

		// setup sources
		$sources = new Minify_Source(array(
			'id'              => 'source1',
			'getContentFunc'  => 'src1_fetch',
			'contentType'	 => Minify::TYPE_CSS,
			'lastModified'    => $nowTime
		));

		// handle request
		$serveArr = array(
			'files'              => $sources,
			'maxAge'             => $maxAge,
			'quiet'              => true,
			'debug'              => true,
			'encodeMethod'       => '',
			'contentTypeCharset' => false
		);

		switch(sysConfig::get('TEMPLATE_STYLESHEET_COMPRESSION')){
			case 'gzip':
				//ob_start("ob_gzhandler");
				break;
			case 'min':
				$serveArr['debug'] = false;
				break;
			case 'min_gzip':
				//ob_start("ob_gzhandler");
				$serveArr['debug'] = false;
				break;
		}
		$Result = Minify::serve('Files', $serveArr);
	}

	$StylesheetCache->setContent($Result['content']);
	$StylesheetCache->setAddedHeaders($Result['headers']);
	if ($noCache === false && sysConfig::get('TEMPLATE_STYLESHEET_CACHE') == 1){
		$StylesheetCache->store();
	}

	$StylesheetCache->output(false, true);

	include('includes/application_bottom.php');
}
