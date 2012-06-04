<?php
/**
 * Sales Igniter E-Commerce System
 * Version: {ses_version}
 *
 * I.T. Web Experts
 * http://www.itwebexperts.com
 *
 * Copyright (c) {ses_copyright} I.T. Web Experts
 *
 * This script and its source are not distributable without the written consent of I.T. Web Experts
 */

function parseElement(&$el, &$parent) {
	global $Layout;

	if (!is_object($parent)){
		$Container = $Layout->Containers->getTable()->create();
		$Layout->Containers->add($Container);
	}
	elseif ($el->hasClass('container')) {
		$Container = $parent->Children->getTable()->create();
		$parent->Children->add($Container);
	}
	else {
		$Container = $parent->Columns->getTable()->create();
		$parent->Columns->add($Container);
	}
	if ($Container->Styles){
		$Container->Styles->clear();
	}
	if ($Container->Configuration){
		$Container->Configuration->clear();
	}
	$Container->sort_order = (int)$el->attr('data-sort_order');

	// process css for id and classes
	if ($el->attr('data-styles')){
		$Styles = json_decode(urldecode($el->attr('data-styles')));
		$InputVals = json_decode(urldecode($el->attr('data-inputs')));

		foreach($Styles as $k => $v){
			if ($k == 'boxShadow'){
				continue;
			}
			if (substr($k, 0, 10) == 'background'){
				continue;
			}

			$Style = $Container->Styles->getTable()->create();
			$Style->definition_key = $k;
			if (is_array($v) || is_object($v)){
				$Style->definition_value = json_encode($v);
			}
			else {
				$Style->definition_value = $v;
			}
			$Container->Styles->add($Style);
		}

		if (!empty($InputVals)){
			foreach($InputVals as $k => $v){
				if ($k == 'boxShadow'){
					continue;
				}

				$Configuration = $Container->Configuration->getTable()->create();
				$Configuration->configuration_key = $k;
				if (is_array($v) || is_object($v)){
					$Configuration->configuration_value = json_encode($v);
				}
				else {
					$Configuration->configuration_value = $v;
				}
				$Container->Configuration->add($Configuration);
			}
		}
	}

	foreach($el->children() as $child){
		$childObj = pq($child);
		if ($childObj->is('ul')){
		}
		else {
			$newParent = ($el->hasClass('column') ? null : (isset($Container) ? $Container : null));
			parseElement($childObj, $newParent);
		}
	}
}

$TemplateLayouts = Doctrine_Core::getTable('TemplateManagerLayouts');
$TemplatePages = Doctrine_Core::getTable('TemplatePages');

if (isset($_GET['layout_id'])){
	$Layout = $TemplateLayouts->find((int)$_GET['layout_id']);
}
else {
	$Layout = $TemplateLayouts->create();
	$Layout->template_id = (int)$_GET['template_id'];
}
$Layout->layout_name = $_POST['layoutName'];
$Layout->layout_type = $_POST['layoutType'];
$Layout->page_type = $_POST['pageType'];

if (isset($_POST['layout_template'])){
	$LayoutTplDir = sysConfig::getDirFsCatalog() . 'extensions/templateManager/layoutTemplates/' . $_POST['layout_template'] . '/';
	$TemplateLayoutSource = file_get_contents($LayoutTplDir . 'layout_content_source.php');
	if ($Layout->page_type == 'print' || $Layout->page_type == 'email'){
		$layoutWidth = '800';
	}elseif ($Layout->page_type == 'template' || $Layout->page_type == 'page'){
		if ($Layout->layout_type == 'desktop'){
			$layoutWidth = '960';
		}
		elseif ($Layout->layout_type == 'smartphone') {
			$layoutWidth = '480';
		}
		elseif ($Layout->layout_type == 'tablet') {
			$layoutWidth = '960';
		}
	}
	$TemplateLayoutSource = str_replace('{$LAYOUT_WIDTH}', $layoutWidth, $TemplateLayoutSource);

	$TemplateLayout = phpQuery::newDocumentHTML($TemplateLayoutSource);
	foreach($TemplateLayout->children() as $child){
		$childObj = pq($child);
		$parent = null;
		parseElement($childObj, $parent);
	}
}

$Layout->save();
$layoutId = $Layout->layout_id;
$layoutName = $Layout->layout_name;
$layoutType = $Layout->layout_type;

$Reset = $TemplatePages->findAll();

foreach($Reset as $rInfo){
	$layouts = explode(',', $rInfo->layout_id);
	if (in_array($layoutId, $layouts)){
		foreach($layouts as $idx => $id){
			if ($id == $layoutId){
				unset($layouts[$idx]);
			}

			if ($id == ''){
				unset($layouts[$idx]);
			}
		}
		$rInfo->layout_id = implode(',', $layouts);
		$rInfo->save();
	}
}

if ($Layout->page_type == 'page'){
	$Layout->layout_settings = json_encode(array(
		'appName' => $_POST['appName'],
		'appPageName' => $_POST['appPageName'],
		'appPageTitle' => array(
			Session::get('languages_id') => $_POST['appPageTitle']
		),
		'appPageSubTitle' => array(
			Session::get('languages_id') => $_POST['appPageSubTitle']
		)
	));
	$Layout->save();
}
elseif ($Layout->page_type == 'print'){
	$Layout->layout_settings = json_encode(array(
		'layoutOrientation' => $_POST['layoutOrientation'],
		'printModules' => $_POST['print_modules']
	));
	$Layout->save();
}
elseif ($Layout->page_type == 'email'){
	$Layout->layout_settings = json_encode(array(
		'emailTemplates' => $_POST['email_template']
	));
	$Layout->save();
}
elseif ($Layout->page_type == 'template') {
	if (isset($_POST['applications'])){
		foreach($_POST['applications'] as $appName => $Pages){
			if ($appName == 'ext'){
				continue;
			}

			foreach($Pages as $pageName){
				$TemplatePage = $TemplatePages->findOneByApplicationAndPage($appName, $pageName);
				$pageType = !empty($_POST['pagetype'][$appName][$pageName]) ? $_POST['pagetype'][$appName][$pageName] : '';
				$assocurl = !empty($_POST['assocurl'][$appName][$pageName]) ? $_POST['assocurl'][$appName][$pageName] : '';
				$currentPageTypesNew = false;
				$currentAssocNew = false;

				if (!$TemplatePage){
					$TemplatePage = new TemplatePages();
					$TemplatePage->application = $appName;
					$TemplatePage->page = $pageName;
				}

				$currentLayouts = explode(',', $TemplatePage->layout_id);
				$currentPageTypes = explode(',', $TemplatePage->page_type);
				$currentAssoc = explode(',', $TemplatePage->associative_url);

				foreach($currentLayouts as $key => $currentLayout){
					//echo 'inside ' . $layoutId . ' = ' . $currentLayout . "\n";
					if ($layoutId == $currentLayout){
						$currentPageTypesNew[$key] = $pageType;
						$currentAssocNew[$key] = $assocurl;
					}
					else {
						if (isset($currentPageTypes[$key])){
							$currentPageTypesNew[$key] = $currentPageTypes[$key];
						}
						else {
							$currentPageTypesNew[$key] = '';
						}

						if (isset($currentAssoc[$key])){
							$currentAssocNew[$key] = $currentAssoc[$key];
						}
						else {
							$currentAssocNew[$key] = '';
						}
					}
				}

				if (!in_array($layoutId, $currentLayouts)){
					$currentLayouts[] = $layoutId;
					$currentPageTypesNew[] = $pageType;
					$currentAssocNew[] = $assocurl;
				}

				$TemplatePage->page_type = implode(',', $currentPageTypesNew);
				$TemplatePage->associative_url = implode(',', $currentAssocNew);
				$TemplatePage->layout_id = implode(',', $currentLayouts);
				$TemplatePage->save();
			}
		}
	}

	if (isset($_POST['applications']['ext'])){
		foreach($_POST['applications']['ext'] as $extName => $Applications){
			foreach($Applications as $appName => $Pages){
				foreach($Pages as $pageName){
					$TemplatePage = $TemplatePages->findOneByApplicationAndPageAndExtension($appName, $pageName, $extName);
					$pageType = !empty($_POST['pagetype']['ext'][$extName][$pageName]) ? $_POST['pagetype']['ext'][$extName][$pageName] : '';
					$currentPageTypesNew = false;
					$assocurl = !empty($_POST['assocurl']['ext'][$extName][$pageName]) ? $_POST['assocurl']['ext'][$extName][$pageName] : '';
					$currentAssocNew = false;

					if (!$TemplatePage){
						$TemplatePage = new TemplatePages();
						$TemplatePage->application = $appName;
						$TemplatePage->page = $pageName;
						$TemplatePage->extension = $extName;
					}

					$currentLayouts = explode(',', $TemplatePage->layout_id);
					$currentPageTypes = explode(',', $TemplatePage->page_type);
					$currentAssoc = explode(',', $TemplatePage->associative_url);

					foreach($currentLayouts as $key=> $currentLayout){
						if ($layoutId == $currentLayout){
							$currentPageTypesNew[$key] = $pageType;
							$currentAssocNew[$key] = $assocurl;
						}
						else {
							if (isset($currentPageTypes[$key])){
								$currentPageTypesNew[$key] = $currentPageTypes[$key];
							}
							else {
								$currentPageTypesNew[$key] = '';
							}
							if (isset($currentAssoc[$key])){
								$currentAssocNew[$key] = $currentAssoc[$key];
							}
							else {
								$currentAssocNew[$key] = '';
							}
						}
					}

					if (!in_array($layoutId, $currentLayouts)){
						$currentLayouts[] = $layoutId;
						$currentPageTypesNew[] = $pageType;
						$currentAssocNew[] = $assocurl;
					}
					$TemplatePage->page_type = implode(',', $currentPageTypesNew);
					$TemplatePage->associative_url = implode(',', $currentAssocNew);
					$TemplatePage->layout_id = implode(',', $currentLayouts);
					$TemplatePage->save();
				}
			}
		}
	}
}

EventManager::attachActionResponse(array(
	'success'    => true,
	'layoutId'   => $layoutId,
	'layoutName' => $layoutName,
	'layoutType' => (isset($layoutType) ? ucfirst($layoutType) : '')
), 'json');
?>
<?php
/*
$containerStylesInfo = array(
	'styles' => array(
		'background' => 'transparent',
		'width' => '960px',
		'color' => '#000000',
		'font-family' => 'Arial',
		'font-size' => '1em',
		'line-height' => '1em',
		'margin-top' => '0px',
		'margin-right' => 'auto',
		'margin-bottom' => '0px',
		'margin-left' => 'auto',
		'padding-top' => '0px',
		'padding-right' => '0px',
		'padding-bottom' => '0px',
		'padding-left' => '0px',
		'border-top-width' => '0px',
		'border-top-color' => '#000000',
		'border-top-style' => 'solid',
		'border-right-width' => '0px',
		'border-right-color' => '#000000',
		'border-right-style' => 'solid',
		'border-bottom-width' => '0px',
		'border-bottom-color' => '#000000',
		'border-bottom-style' => 'solid',
		'border-left-width' => '0px',
		'border-left-color' => '#000000',
		'border-left-style' => 'solid',
		'border-top-left-radius' => '0px',
		'border-top-right-radius' => '0px',
		'border-bottom-left-radius' => '0px',
		'border-bottom-right-radius' => '0px'
	),
	'inputVals' => array(
		'id' => 'theContainer0',
		'equal_heights' => '',
		'width' => '960',
		'width_unit' => 'px',
		'color' => '#000000',
		'font_family' => 'Arial',
		'font_size' => '1',
		'font_size_unit' => 'em',
		'line_height' => '1',
		'line_height_unit' => 'em',
		'text_align' => null,
		'margin_top' => '0',
		'margin_top_unit' => 'px',
		'margin_right' => '0',
		'margin_right_unit' => 'auto',
		'margin_bottom' => '0',
		'margin_bottom_unit' => 'px',
		'margin_left' => '0',
		'margin_left_unit' => 'auto',
		'padding_top' => '0',
		'padding_top_unit' => 'px',
		'padding_right' => '0',
		'padding_right_unit' => 'px',
		'padding_bottom' => '0',
		'padding_bottom_unit' => 'px',
		'padding_left' => '0',
		'padding_left_unit' => 'px',
		'border_top_width' => '0',
		'border_top_width_unit' => 'px',
		'border_top_color' => '#000000',
		'border_top_style' => 'solid',
		'border_right_width' => '0',
		'border_right_width_unit' => 'px',
		'border_right_color' => '#000000',
		'border_right_style' => 'solid',
		'border_bottom_width' => '0',
		'border_bottom_width_unit' => 'px',
		'border_bottom_color' => '#000000',
		'border_bottom_style' => 'solid',
		'border_left_width' => '0',
		'border_left_width_unit' => 'px',
		'border_left_color' => '#000000',
		'border_left_style' => 'solid',
		'border_top_left_radius' => '0',
		'border_top_left_radius_unit' => 'px',
		'border_top_right_radius' => '0',
		'border_top_right_radius_unit' => 'px',
		'border_bottom_left_radius' => '0',
		'border_bottom_left_radius_unit' => 'px',
		'border_bottom_right_radius' => '0',
		'border_bottom_right_radius_unit' => 'px',
		'background_type' => 'transparent',
		'classes' => '',
		'custom_css' => '',
		'enable_advanced' => '',
		'float' => null,
		'position' => null,
		'top' => '',
		'top_unit' => null,
		'right' => '',
		'right_unit' => null,
		'bottom' => '',
		'bottom_unit' => null,
		'left' => '',
		'left_unit' => null,
		'overflow_x' => null,
		'overflow_y' => null,
		'z_index' => ''
	)
)
*/
?>
