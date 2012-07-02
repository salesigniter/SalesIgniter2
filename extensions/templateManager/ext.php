<?php
class Extension_templateManager extends ExtensionBase
{

	private $widgetPaths = array();

	private $widgetTemplatePaths = array();

	private $printWidgetPaths = array();

	private $printWidgetTemplatePaths = array();

	public function __construct() {
		parent::__construct('templateManager');
		require(__DIR__ . '/widgets/TemplateManagerWidgetBase.php');
		require(__DIR__ . '/widgets/TemplateManagerWidget.php');
		require(__DIR__ . '/widgets/TemplateManagerPrintWidget.php');
		require(__DIR__ . '/widgets/TemplateManagerLabelWidget.php');
	}

	public function init() {
		global $appExtension;
		if ($this->isEnabled() === false) {
			return;
		}

		EventManager::attachEvents(array(
			'BoxWebsiteAddLink'
		), null, $this);
	}

	public function BoxWebsiteAddLink(&$contents){
		$contents['children'][] = array(
			'link' => false,
			'text' => 'Template Manager',
			'children' => array(
				array(
					'link' => itw_app_link('appExt=templateManager', 'layout_manager', 'default'),
					'text' => 'Manage Layouts',
				)
			)
		);
	}

	public function onLoad() {
		global $templateDir;
		if (APPLICATION_ENVIRONMENT == 'admin'){
			Session::set('tplDir', 'fallback');
			$templateDir = 'fallback';

			$TemplateConfig = array(
				'ID' => 0,
				'NAME' => array('configuration_value' => 'Fallback'),
				'DIRECTORY' => array('configuration_value' => 'fallback'),
				//'TEMPLATE_TYPE' => array('configuration_value' => 'desktop'),
				'STYLESHEET_CACHE' => array('configuration_value' => 1),
				'STYLESHEET_COMPRESSION' => array('configuration_value' => 'min'),
				'JAVASCRIPT_CACHE' => array('configuration_value' => 1),
				'JAVASCRIPT_COMPRESSION' => array('configuration_value' => 'min')
				);
		}
		else {
			if (isset($_GET['tplDir'])){
				$templateDir = $_GET['tplDir'];
			}else{
				$templateDir = sysConfig::get('DIR_WS_TEMPLATES_DEFAULT');
			}

			EventManager::notify('SetTemplateName');

			$TemplateID = Doctrine_Query::create()
				->select('template_id')
				->from('TemplateManagerTemplatesConfiguration')
				->where('configuration_value = ?', $templateDir)
				->andWhere('configuration_key = ?', 'NAME')
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

			$TemplateConfig = Doctrine_Query::create()
				->from('TemplateManagerTemplatesConfiguration')
				->where('template_id = ?', $TemplateID[0]['template_id'])
				->execute()
				->toArray();

            $TemplateConfig['ID'] = $TemplateID[0]['template_id'];
		}

		sysConfig::set('TEMPLATE_ID', $TemplateConfig['ID']['template_id'], true);
		sysConfig::set('TEMPLATE_NAME', $TemplateConfig['NAME']['configuration_value'], true);
		sysConfig::set('TEMPLATE_DIRECTORY', $TemplateConfig['DIRECTORY']['configuration_value'], true);
		//sysConfig::set('TEMPLATE_TYPE', $TemplateConfig['TEMPLATE_TYPE']['configuration_value'], true);
		sysConfig::set('TEMPLATE_STYLESHEET_CACHE', $TemplateConfig['STYLESHEET_CACHE']['configuration_value'], true);
		sysConfig::set('TEMPLATE_STYLESHEET_COMPRESSION', $TemplateConfig['STYLESHEET_COMPRESSION']['configuration_value'], true);
		sysConfig::set('TEMPLATE_JAVASCRIPT_CACHE', $TemplateConfig['JAVASCRIPT_CACHE']['configuration_value'], true);
		sysConfig::set('TEMPLATE_JAVASCRIPT_COMPRESSION', $TemplateConfig['JAVASCRIPT_COMPRESSION']['configuration_value'], true);

		if (APPLICATION_ENVIRONMENT == 'admin'){
			sysConfig::set('DIR_WS_TEMPLATE', sysConfig::getDirWsAdmin() . 'template/' . sysConfig::get('TEMPLATE_DIRECTORY') . '/', true, true);
			sysConfig::set('DIR_WS_TEMPLATE_IMAGES', sysConfig::get('DIR_WS_TEMPLATES') . 'images/');
			sysConfig::set('DIR_FS_TEMPLATE', sysConfig::getDirFsAdmin() . 'template/' . sysConfig::get('TEMPLATE_DIRECTORY') . '/', true, true);
			sysConfig::set('DIR_FS_TEMPLATE_IMAGES', sysConfig::get('DIR_FS_TEMPLATES') . 'images/');

			sysConfig::set('DIR_WS_CATALOG_TEMPLATES', sysConfig::getDirWsCatalog() . 'templates/', true, true);
			sysConfig::set('DIR_FS_CATALOG_TEMPLATES', sysConfig::getDirFsCatalog() . 'templates/', true, true);
		}else{
			sysConfig::set('DIR_WS_TEMPLATE', sysConfig::getDirWsCatalog() . 'templates/' . sysConfig::get('TEMPLATE_DIRECTORY') . '/', true, true);
			sysConfig::set('DIR_WS_TEMPLATE_IMAGES', sysConfig::get('DIR_WS_TEMPLATES') . 'images/');
			sysConfig::set('DIR_FS_TEMPLATE', sysConfig::getDirFsCatalog() . 'templates/' . sysConfig::get('TEMPLATE_DIRECTORY') . '/');
			sysConfig::set('DIR_FS_TEMPLATE_IMAGES', sysConfig::get('DIR_FS_TEMPLATES') . 'images/');
		}

		if ((preg_match('/^[[:alnum:]|_|-]+$/', sysConfig::get('TEMPLATE_NAME'))) && (is_dir(sysConfig::get('DIR_FS_TEMPLATE')))){
		}
		else {
			echo strip_tags(sysConfig::get('TEMPLATE_DIRECTORY')) . '<br>';
			exit('Illegal template directory!');
		}
	}

	public function getLayoutBuilder(){
		if (!class_exists('TemplateManagerLayoutBuilder')){
			require(__DIR__ . '/classes/layoutBuilder.php');
		}
		return new TemplateManagerLayoutBuilder();
	}

	public function buildLayout(&$Construct, $layoutId){
		global $Editor;
		$LayoutBuilder = $this->getLayoutBuilder();
		$LayoutBuilder->setLayoutId($layoutId);
		$LayoutBuilder->addVar('Sale', $Editor);
		$LayoutBuilder->build($Construct);
	}

	public function parseItemLink($Data){
		$return = '';
		if ($Data !== false){
			if ($Data->type == 'app'){
				$getParams = null;
				if (stristr($Data->app->name, '/')){
					$extInfo = explode('/', $Data->app->name);
					$application = $extInfo[1];
					$getParams = 'appExt=' . $extInfo[0];
				}
				else {
					$application = $Data->app->name;
				}

				$return = itw_app_link($getParams, $application, $Data->app->page);
			}
			elseif ($Data->type == 'category'){
				$return = itw_app_link('cPath=' . str_replace('_none', '', implode('_', $Data->category->id)), 'index', 'default');
			}
			elseif ($Data->type == 'custom') {
				$return = $Data->url;
			}
		}
		return $return;
	}
}

/* @TODO: Find a better place for this stuff */
global $jqueryThemeDir, $jqueryThemeBG, $jqueryThemeIcons, $jqueryThemeImages, $templateDir;

$jqueryThemeDir = sysConfig::getDirWsCatalog() . 'ext/jQuery/themes/smoothness/';
$jqueryThemeBG = sysConfig::getDirWsCatalog() . 'ext/jQuery/themes/smoothness/';
$jqueryThemeIcons = sysConfig::getDirWsCatalog() . 'ext/jQuery/themes/icons';
$jqueryThemeImages = sysConfig::getDirWsCatalog() . 'ext/jQuery/themes/smoothness/images';

function jqueryIconsPath($color) {
	global $jqueryThemeIcons;
	return $jqueryThemeIcons . '/ui-icons_' . $color . '_256x240.png';
}

function matchEngineVersion($engine, $v) {
	$u_agent = $_SERVER['HTTP_USER_AGENT'];
	$matched = false;
	$vInfo = array();
	preg_match_all('/' . $engine . '\/(.*)\)/', $u_agent, $vInfo);
	if ((int)$vInfo[1][0] == $v){
		$matched = true;
	}
	return $matched;
}

function matchUserAgent($toMatch) {
	$u_agent = $_SERVER['HTTP_USER_AGENT'];
	$ub = false;
	if (preg_match('/' . $toMatch . '/i', $u_agent)){
		$ub = true;
	}
	return $ub;
}

function isIE() {
	return matchUserAgent('MSIE');
}

/* Trident/3.0 */
function isIE7() {
	return (isIE() ? matchEngineVersion('Trident', 3) : false);
}

/* Trident/4.0 */
function isIE8() {
	return (isIE() ? matchEngineVersion('Trident', 4) : false);
}

/* Trident/5.0 */
function isIE9() {
	return (isIE() ? matchEngineVersion('Trident', 5) : false);
}

/* Trident/6.0 */
function isIE10() {
	return (isIE() ? matchEngineVersion('Trident', 6) : false);
}

function isMoz() {
	return (matchUserAgent('Mozilla') && !matchUserAgent('AppleWebKit'));
}

function isChrome() {
	return (isWebkit() ? matchUserAgent('Chrome') : false);
}

function isSafari() {
	return (isWebkit() ? (!matchUserAgent('Chrome') && matchUserAgent('Safari')) : false);
}

function isWebkit() {
	return matchUserAgent('AppleWebKit');
}

function isPresto() {
	$u_agent = $_SERVER['HTTP_USER_AGENT'];
	$ub = false;
	if (matchUserAgent('Presto')){
		$vInfo = array();
		preg_match_all('/Presto\/(.*) Version/', $u_agent, $vInfo);
		if ($vInfo[1][0] > 2.07){
			$ub = true;
		}
	}
	return $ub;
}

function buildBackgroundAlpha($r, $g, $b, $a, &$styleObj = false) {
	$cssData = array();
	if (isIE8() === true){
		//$cssData['-pie-background'] = 'rgba(' . $r . ', ' . $g . ', ' . $b . ', ' . $a . ')';
		//$cssData['behavior'] = 'url(' . sysConfig::getDirWsCatalog() . 'ext/ie_behave/PIE.htc)';
		$cssData['background-color'] = 'rgb(' . $r . ', ' . $g . ', ' . $b . ')';
	}
	else {
		$cssData['background-color'] = 'rgba(' . $r . ', ' . $g . ', ' . $b . ', ' . $a . ')';
	}

	$css = '';
	foreach($cssData as $bgKey => $bgInfo){
		if ($styleObj !== false){
			$styleObj->addRule($bgKey, $bgInfo);
		}
		else {
			$css .= $bgKey . ': ' . $bgInfo . ';';
		}
	}
	return $css;
}

function buildSimpleGradient($start, $end, &$styleObj = false) {
	return buildLinearGradient(270, array(
		array($start, 0),
		array($end, 1)
	), $styleObj);
}

function buildLinearGradient($deg, $colorStops, $images = false, &$styleObj = false) {
	$cssData = array();
	if (isIE7() === true){
		$stops = array();
		foreach($colorStops as $cInfo){
			$stops[] = $cInfo[0] . ' ' . ($cInfo[1] * 100) . '%';
		}

		if ($images !== false){
			foreach($images as $iInfo){
				if (isset($iInfo['css_placement']) && $iInfo['css_placement'] == 'after'){
					continue;
				}

				$cssData['-pie-background'][] = 'url(' . $iInfo['image'] . ') ' .
					$iInfo['repeat'] . ' ' .
					(isset($iInfo['attachment']) ? $iInfo['attachment'] . ' ' : 'scroll ') .
					$iInfo['pos_x'] . ' ' . $iInfo['pos_y'];
			}
		}
		$cssData['-pie-background'][] = 'linear-gradient(' . $deg . 'deg, ' . implode(', ', $stops) . ')';
		if ($images !== false){
			foreach($images as $iInfo){
				if (isset($iInfo['css_placement']) && $iInfo['css_placement'] == 'before'){
					continue;
				}

				$cssData['-pie-background'][] = 'url(' . $iInfo['image'] . ') ' .
					$iInfo['repeat'] . ' ' .
					(isset($iInfo['attachment']) ? $iInfo['attachment'] . ' ' : 'scroll ') .
					$iInfo['pos_x'] . ' ' . $iInfo['pos_y'];
			}
		}
		//$cssData['behavior'][] = 'url(' . sysConfig::getDirWsCatalog() . 'ext/ie_behave/PIE.htc)';
	}
	elseif (isIE8() === true) {
		$stops = array();
		foreach($colorStops as $cInfo){
			$stops[] = array(
				'pos'     => ($cInfo[1] * 100),
				'color'   => $cInfo[0],
				'opacity' => 100
			);
		}

		$cssData['background-image'][] = 'url(' . sysConfig::getDirWsCatalog() . 'extensions/templateManager/catalog/globalFiles/IE8_gradient.php?width=10&height=100&angle=' . $deg . '&colorStops=' . urlencode(json_encode($stops)) . ')';
		$cssData['-jquery'][] = 'if ($(this).height() > 10){ $(this).css(\'background-image\', \'url(extensions/templateManager/catalog/globalFiles/IE8_gradient.php?width=\' + $(this).outerWidth(true) + \'&height=\' + $(this).outerHeight(true) + \'&angle=' . $deg . '&colorStops=' . urlencode(json_encode($stops)) . ')\'); }';
		$cssData['background-repeat'][] = 'repeat-x';
		$cssData['background-attachment'][] = (isset($iInfo['attachment']) ? $iInfo['attachment'] : 'scroll');
		$cssData['background-position'][] = '0% 0%';
	}
	elseif (isSafari() === true) {
		if ($images !== false){
			foreach($images as $iInfo){
				if (isset($iInfo['css_placement']) && $iInfo['css_placement'] == 'after'){
					continue;
				}

				$cssData['background'][] = 'url(' . $iInfo['image'] . ')';
				$cssData['background-repeat'][] = $iInfo['repeat'];
				$cssData['background-attachment'][] = (isset($iInfo['attachment']) ? $iInfo['attachment'] : 'scroll');
				$cssData['background-position'][] = $iInfo['pos_x'] . ' ' . $iInfo['pos_y'];
			}
		}

		$stops = array();
		foreach($colorStops as $cInfo){
			$stops[] = 'color-stop(' . $cInfo[1] . ', ' . $cInfo[0] . ')';
		}

		$angle = $deg . 'deg';
		switch($deg){
			case 0:
				$angle = 'left';
				break;
			case 45:
				$angle = 'bottom left';
				break;
			case 90:
				$angle = 'bottom';
				break;
			case 135:
				$angle = 'bottom right';
				break;
			case 190:
				$angle = 'right';
				break;
			case 235:
				$angle = 'top right';
				break;
			case 270:
				$angle = 'left top, left bottom';
				break;
			case 315:
				$angle = 'top left';
				break;
			case 360:
				$angle = 'left';
				break;
		}

		$cssData['background'][] = '-webkit-gradient(linear, ' . $angle . ', ' . implode(', ', $stops) . ')';
		$cssData['background-repeat'][] = 'repeat-x';
		$cssData['background-attachment'][] = (isset($iInfo['attachment']) ? $iInfo['attachment'] : 'scroll');
		$cssData['background-position'][] = '0% 0%';

		if ($images !== false){
			foreach($images as $iInfo){
				if (!isset($iInfo['css_placement']) || $iInfo['css_placement'] == 'before'){
					continue;
				}

				$cssData['background'][] = 'url(' . $iInfo['image'] . ')';
				$cssData['background-repeat'][] = $iInfo['repeat'];
				$cssData['background-attachment'][] = (isset($iInfo['attachment']) ? $iInfo['attachment'] : 'scroll');
				$cssData['background-position'][] = $iInfo['pos_x'] . ' ' . $iInfo['pos_y'];
			}
		}
	}
	else {
		if ($images !== false){
			foreach($images as $iInfo){
				if (isset($iInfo['css_placement']) && $iInfo['css_placement'] == 'after'){
					continue;
				}

				$cssData['background'][] = 'url(' . $iInfo['image'] . ')';
				$cssData['background-repeat'][] = $iInfo['repeat'];
				$cssData['background-attachment'][] = (isset($iInfo['attachment']) ? $iInfo['attachment'] : 'scroll');
				$cssData['background-position'][] = $iInfo['pos_x'] . ' ' . $iInfo['pos_y'];
			}
		}

		if (isIE9() === true){
			$stops = array();
			foreach($colorStops as $cInfo){
				$stops[] = array(
					'pos'     => ($cInfo[1] * 100),
					'color'   => $cInfo[0],
					'opacity' => 100
				);
			}
			$backgroundStr = 'data:image/svg+xml;base64,' . base64_encode(buildSvgGradientContent($deg, $stops));
			$cssData['background'][] = 'url(' . $backgroundStr . ')';
			//$cssData['background'][] = 'url(/extensions/templateManager/catalog/globalFiles/IE9_gradient.php?angle=' . $deg . '&colorStops=' . urlencode(json_encode($stops)) . ')';
			$cssData['background-repeat'][] = 'repeat-x';
			$cssData['background-attachment'][] = (isset($iInfo['attachment']) ? $iInfo['attachment'] : 'scroll');
			$cssData['background-position'][] = '0% 0%';
		}
		else {
			$stops = array();
			foreach($colorStops as $cInfo){
				$stops[] = $cInfo[0] . ' ' . ($cInfo[1] * 100) . '%';
			}

			$prefix = '';
			switch(true){
				case (isIE10() === true):
					$prefix = '-ms-';
					break;
				case (isPresto() === true):
					$prefix = '-o-';
					break;
				case (isMoz() === true):
					$prefix = '-moz-';
					break;
				case (isWebkit() === true):
					$prefix = '-webkit-';
					break;
			}

			$angle = $deg . 'deg';
			switch($deg){
				case 0:
					$angle = 'left';
					break;
				case 45:
					$angle = 'bottom left';
					break;
				case 90:
					$angle = 'bottom';
					break;
				case 135:
					$angle = 'bottom right';
					break;
				case 190:
					$angle = 'right';
					break;
				case 235:
					$angle = 'top right';
					break;
				case 270:
					$angle = 'top';
					break;
				case 315:
					$angle = 'top left';
					break;
				case 360:
					$angle = 'left';
					break;
			}

			$cssData['background'][] = $prefix . 'linear-gradient(' . $angle . ', ' . implode(', ', $stops) . ')';
			$cssData['background-repeat'][] = 'repeat-x';
			$cssData['background-attachment'][] = (isset($iInfo['attachment']) ? $iInfo['attachment'] : 'scroll');
			$cssData['background-position'][] = '0% 0%';
		}

		if ($images !== false){
			foreach($images as $iInfo){
				if (!isset($iInfo['css_placement']) || $iInfo['css_placement'] == 'before'){
					continue;
				}

				$cssData['background'][] = 'url(' . $iInfo['image'] . ')';
				$cssData['background-repeat'][] = $iInfo['repeat'];
				$cssData['background-attachment'][] = (isset($iInfo['attachment']) ? $iInfo['attachment'] : 'scroll');
				$cssData['background-position'][] = $iInfo['pos_x'] . ' ' . $iInfo['pos_y'];
			}
		}
	}
	$css = '';
	foreach($cssData as $bgKey => $bgInfo){
		if ($styleObj !== false){
			$styleObj->addRule($bgKey, implode(', ', $bgInfo));
		}
		else {
			$css .= $bgKey . ': ' . implode(', ', $bgInfo) . ';';
		}
	}
	return $css;
}

function buildBorderRadius($tl = 0, $tr = 0, $br = 0, $bl = 0, &$styleObj = false) {
	$cssData = array();
	$prefix = '';
	switch(true){
		case (isIE() === false && isMoz() === true):
			$prefix = '-moz-';
			break;
		case (isWebkit() === true):
			$prefix = '-webkit-';
			break;
	}
	$cssData[$prefix . 'border-radius'] = $tl . ' ' . $tr . ' ' . $br . ' ' . $bl;
	if (isIE8() === true){
		//$cssData['behavior'] = 'url(' . sysConfig::getDirWsCatalog() . 'ext/ie_behave/PIE.htc)';
	}

	$css = '';
	foreach($cssData as $bgKey => $bgInfo){
		if ($styleObj !== false){
			$styleObj->addRule($bgKey, $bgInfo);
		}
		else {
			$css .= $bgKey . ': ' . $bgInfo . ';';
		}
	}
	return $css;
}

function buildBoxShadow($shadows, &$styleObj = false) {
	$cssData = array();

	$allShadows = array();
	foreach($shadows as $sInfo){
		$allShadows[] = (isset($sInfo[5]) && $sInfo[5] === true ? 'inset ' : '') .
			$sInfo[0] . ' ' .
			$sInfo[1] . ' ' .
			$sInfo[2] . ' ' .
			$sInfo[3] . ' ' .
			$sInfo[4];
	}

	$prefix = '';
	switch(true){
		case (isMoz() === true):
			$prefix = '-moz-';
			break;
		case (isWebkit() === true):
			$prefix = '-webkit-';
			break;
	}
	$cssData[$prefix . 'box-shadow'] = implode(', ', $allShadows);

	$css = '';
	foreach($cssData as $bgKey => $bgInfo){
		if ($styleObj !== false){
			$styleObj->addRule($bgKey, $bgInfo);
		}
		else {
			$css .= $bgKey . ': ' . $bgInfo . ';';
		}
	}
	return $css;
}

function buildTextShadow($shadows, &$styleObj = false) {
	$cssData = array();

	$allShadows = array();
	foreach($shadows as $sInfo){
		$allShadows[] = $sInfo[0] . 'px ' .
			$sInfo[1] . 'px ' .
			$sInfo[2] . 'px ' .
			$sInfo[3];
	}

	$cssData['text-shadow'] = implode(', ', $allShadows);

	$css = '';
	foreach($cssData as $bgKey => $bgInfo){
		if ($styleObj !== false){
			$styleObj->addRule($bgKey, $bgInfo);
		}
		else {
			$css .= $bgKey . ': ' . $bgInfo . ';';
		}
	}
	return $css;
}

function gradientDegreesToArray($deg){
	$xStart = 0;
	$yStart = 0;
	$xEnd = 0;
	$yEnd = 0;
	switch($deg){
		case '0':
			$xStart = 0;
			$yStart = 0;
			$xEnd = 100;
			$yEnd = 0;
			break;
		case '45':
			$xStart = 0;
			$yStart = 100;
			$xEnd = 100;
			$yEnd = 0;
			break;
		case '90':
			$xStart = 0;
			$yStart = 100;
			$xEnd = 0;
			$yEnd = 0;
			break;
		case '135':
			$xStart = 100;
			$yStart = 100;
			$xEnd = 0;
			$yEnd = 0;
			break;
		case '180':
			$xStart = 100;
			$yStart = 0;
			$xEnd = 0;
			$yEnd = 0;
			break;
		case '225':
			$xStart = 100;
			$yStart = 0;
			$xEnd = 0;
			$yEnd = 100;
			break;
		case '270':
			$xStart = 100;
			$yStart = 0;
			$xEnd = 100;
			$yEnd = 100;
			break;
		case '315':
			$xStart = 0;
			$yStart = 0;
			$xEnd = 100;
			$yEnd = 100;
			break;
		case '360':
			$xStart = 0;
			$yStart = 0;
			$xEnd = 100;
			$yEnd = 0;
			break;
	}

	return array(
		'x_start' => $xStart,
		'y_start' => $yStart,
		'x_end' => $xEnd,
		'y_end' => $yEnd
	);
}

function colorStopArrayToSvg($colorStops){
	$return = '';
	foreach($colorStops as $sInfo){
		$color = $sInfo['color'];
		$opacity = $sInfo['opacity'];
		if (substr($color, 0, 4) == 'rgba'){
			$matches = array();
			preg_match_all('/rgba\((.*),[\s?](.*),[\s?](.*),[\s?](.*)\)/', $color, $matches);
			$color = 'rgb(' . $matches[1][0] . ', ' . $matches[2][0] . ', ' . $matches[3][0] . ')';
			$opacity = $matches[4][0];
		}

		$return .= '<stop offset="' . $sInfo['pos'] . '%" stop-color="' . $color . '" stop-opacity="' . $opacity . '" />';
	}
	return $return;
}

function buildSvgGradientContent($deg, $colorStops) {
	$xy = gradientDegreesToArray($deg);
	$random = rand(500, 1000);
	$svgStr = '<svg ' .
		'xmlns="http://www.w3.org/2000/svg" ' .
		'width="100%" ' .
		'height="100%" ' .
		'viewBox="0 0 1 1" ' .
		'preserveAspectRatio="none"' .
		'>' .
		'<linearGradient ' .
		'id="gradient' . $random . '" ' .
		'gradientUnits="userSpaceOnUse" ' .
		'x1="' . $xy['x_start'] . '%" ' .
		'y1="' . $xy['y_start'] . '%" ' .
		'x2="' . $xy['x_end'] . '%" ' .
		'y2="' . $xy['y_end'] . '%"' .
		'>' .
		colorStopArrayToSvg($colorStops) .
		'</linearGradient>' .
		'<rect ' .
		'x="0" ' .
		'y="0" ' .
		'width="100%" ' .
		'height="100%" ' .
		'fill="url(#gradient' . $random . ')"' .
		'/>' .
		'</svg>';
	return $svgStr;
}

function buildSvgTextGradientContent($deg, $colorStops, $text, $fontSize) {
	$xy = gradientDegreesToArray($deg);
	$random = rand(500, 1000);
	$svgStr = '<svg ' .
			'xmlns="http://www.w3.org/2000/svg" ' .
			'width="100%" ' .
			'height="100%" ' .
			'viewBox="0 0 1 1" ' .
			'preserveAspectRatio="none"' .
		'>' .
		'<linearGradient ' .
			'id="text_gradient' . $random . '" ' .
			'gradientUnits="userSpaceOnUse" ' .
			'x1="' . $xy['x_start'] . '%" ' .
			'y1="' . $xy['y_start'] . '%" ' .
			'x2="' . $xy['x_end'] . '%" ' .
			'y2="' . $xy['y_end'] . '%"' .
		'>' .
		colorStopArrayToSvg($colorStops) .
		'</linearGradient>' .
		'<g transform="translate(50,150)">' .
			'<text ' .
				'id="horizontalText' . $random . '" ' .
				'x="0" ' .
				'y="0" ' .
				'fill="url(#text_gradient' . $random . ')" ' .
				'font-size="' . $fontSize . '"' .
			'>' .
			$_GET['string'] .
			'</text>' .
		'</g>' .
		'</svg>';
	return $svgStr;
}
