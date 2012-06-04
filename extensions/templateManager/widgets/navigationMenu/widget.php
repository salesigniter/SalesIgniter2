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

class TemplateManagerWidgetNavigationMenu extends TemplateManagerWidget
{

	public function __construct() {
		global $App;
		$this->init('navigationMenu');
		$this->firstAdded = false;
		$this->buildStylesheetMultiple = false;
		$this->buildJavascriptMultiple = true;
	}

	public function showLayoutPreview($WidgetSettings) {
		$return = '';
		if (isset($WidgetSettings['settings']->menuSettings) && !empty($WidgetSettings['settings']->menuSettings)){
			$menuItems = '';
			$MenuSettings = array();
			foreach($WidgetSettings['settings']->menuSettings as $mInfo){
				$MenuSettings[] = $mInfo;
			}

			foreach($MenuSettings as $k => $mInfo){
				$Text = $mInfo->link->text->{Session::get('languages_id')};
				$css = 'display:inline-block;';
				if ($WidgetSettings->forceFit == 'true'){
					$css .= 'width: ' . (100 / sizeof($MenuSettings)) . '%;';
				}
				$menuItems .= '<span style="' . $css . '">' . $Text . '</span>';
			}
			$return = $menuItems;
		}else{
			$return = $this->getTitle();
		}
		return $return;
	}

	private function checkCondition($condition){
		global $ShoppingCart, $userAccount;
		switch($condition){
			case 'customer_logged_in':
				if ($userAccount->isLoggedIn() === false){
					return false;
				}
				break;
			case 'customer_not_logged_in':
				if ($userAccount->isLoggedIn() === true){
					return false;
				}
				break;
			case 'shopping_cart_empty':
				if ($ShoppingCart->countContents() > 0){
					return false;
				}
				break;
			case 'shopping_cart_not_empty':
				if ($ShoppingCart->countContents() <= 0){
					return false;
				}
				break;
		}
		return true;
	}

	private function parseMenuItem($item, $isRoot = false, $isLast = false) {
		global $appExtension;
		if (isset($item->link->condition) && $this->checkCondition($item->link->condition) === false){
			return '';
		}

		$TemplateManager = $appExtension->getExtension('templateManager');

		$Data = $item->link;
		$icon = '';
		if ($Data->icon == 'jquery'){
			$icon = '<span class="ui-icon ' . $Data->icon_src . '"></span>';
		}
		elseif ($Data->icon == 'custom') {
			$icon = '<img src="' . $Data->icon_src . '">';
		}

		$menuText = '<span class="menu_text">' . $Data->text->{Session::get('languages_id')} . '</span>';

		$itemLink = htmlBase::newElement('a')
			->addClass('ui-corner-all')
			->setHref($TemplateManager->parseItemLink($Data))
			->html($icon . $menuText);
		if ($Data !== false){
			if ($Data->type != 'none'){
				if ($Data->target == 'new'){
					$itemLink->attr('target', '_blank');
				}
				elseif ($Data->target == 'dialog') {
					$itemLink->attr('onclick', 'Javascript:popupWindow(this.href);');
				}
			}
		}

		$addCls = 'ui-state-default';
		if ($isRoot === true){
			$addCls .= ' root';
		}

		if ($this->firstAdded === false){
			$addCls .= ' first';
			$this->firstAdded = true;
		}
		elseif ($isLast === true) {
			$addCls .= ' last';
		}
		else {
			$addCls .= ' middle';
		}

		if (isset($_GET['cPath']) && $Data->type == 'category'){
			$path = str_replace('_none', '', implode('_', $Data->category->id));
			if ($_GET['cPath'] == $path){
				$addCls .= ' ui-state-active';
			}
		}
		elseif (isset($application) && $App->getAppName() == $application && $App->getPageName() == $Data->app->page){
			$addCls .= ' ui-state-active';
		}
		elseif (isset($application) && $App->getAppName() == $application && isset($_GET['appPage']) && $_GET['appPage'] == $Data->app->page){
			if ($application != 'index' || ($application == 'index' && !isset($_GET['cPath']))){
				$addCls .= ' ui-state-active';
			}
		}

		$itemTemplate = '<li class="' . $addCls . '">';
		if (isset($item->children) && !empty($item->children)){
			$itemTemplate .= $itemLink->draw() . '<span class="ui-icon ui-icon-triangle-1-e"></span>';
			$itemTemplate .= '<ol>';
			foreach($item->children as $k => $childItem){
				$itemTemplate .= $this->parseMenuItem($childItem, false, (!isset($item->children->{$k + 1}) || empty($item->children->{$k + 1})));
			}
			$itemTemplate .= '</ol>';
		}
		else {
			$itemTemplate .= $itemLink->draw();
		}

		$itemTemplate .= '</li>';

		return $itemTemplate;
	}

	public function buildStylesheet() {
		$css = '/* Navigation Menu --BEGIN-- */' . "\n" .
			'.ui-navigation-menu { position:relative;background-color:transparent;border: none;line-height:inherit;font-size:inherit; }' . "\n" .
			'.ui-navigation-menu ol { background-color:transparent;list-style:none;padding:0;margin:0;border:none;line-height:inherit;z-index: 100; }' . "\n" .
			'.ui-navigation-menu li { position:relative;display:block;border:none;background:none;line-height:inherit;text-align:left; }' . "\n" .
			'.ui-navigation-menu li a { width:100%;background-color:transparent;display:inline-block;text-decoration:none;white-space:nowrap; }' . "\n" .
			'.ui-navigation-menu li a span { line-height:1em;background-color:transparent;display:inline-block;vertical-align:baseline; }' . "\n" .
			'.ui-navigation-menu li ol { display:none;position:absolute; }' . "\n" .
			'.ui-navigation-menu li.root { display:inline-block;text-align:center; }' . "\n" .
			'.ui-navigation-menu li.root.first {  }' . "\n" .
			'.ui-navigation-menu li.root.middle { border-left:none; }' . "\n" .
			'.ui-navigation-menu li.root.last { border-left:none; }' . "\n" .
			'.ui-navigation-menu li.root.ui-state-default {  }' . "\n" .
			'.ui-navigation-menu li.root.ui-state-active {  }' . "\n" .
			'.ui-navigation-menu li.root.ui-state-hover {  }' . "\n" .
			'.ui-navigation-menu li ol li.first {  }' . "\n" .
			'.ui-navigation-menu li ol li.middle { border-top:none; }' . "\n" .
			'.ui-navigation-menu li ol li.last { border-top:none; }' . "\n" .
			'.ui-navigation-menu li ol li.ui-state-default {  }' . "\n" .
			'.ui-navigation-menu li ol li.ui-state-active { }' . "\n" .
			'.ui-navigation-menu li ol li.ui-state-hover {  }' . "\n" .
			'.ui-navigation-menu .ui-icon, .ui-navigation-menu img { vertical-align:baseline;display:inline-block; }' . "\n" .
			'.ui-navigation-menu img { margin-right:.3em; }' . "\n" .
			'/* Navigation Menu --END-- */' . "\n";

		return $css;
	}

	public function buildJavascript() {
		$WidgetProperties = $this->loadLinkedSettings($this->getWidgetProperties());

		ob_start();
		?>
		$(document).ready(function (){
	$('#<?php echo $WidgetProperties->menuId; ?>.ui-navigation-menu').each(function (){
	<?php if ($WidgetProperties->forceFit == 'true'){ ?>
		var Roots = [];
		<?php } ?>
	$(this).find('li').each(function (){
	$(this).addClass('ui-state-default');
	$(this).mouseover(function (){
	$(this).addClass('ui-state-hover');

	if ($(this).children('ol').size() > 0){
	var self = $(this);

	$(this).find('ol:first').each(function (i, el){
	var cssSettings = {
	top: 0,
	left: 0,
	zIndex: self.parent().css('z-index') + 1
	};

	if (self.hasClass('root')){
	cssSettings.top = self.innerHeight();
	}else{
	cssSettings.left = '98%';
	}

	$(this).css(cssSettings).show();

	$(this).find('.ui-icon.ui-icon-triangle-1-s').each(function (){
	$(this).removeClass('ui-icon-triangle-1-s').addClass('ui-icon-triangle-1-e').css({
	position: 'absolute',
	right: 0,
	top: (self.innerHeight() / 2) - ($(this).outerHeight() / 2)
	});
	});
	});
	}
	}).mouseout(function (){
	$(this).removeClass('ui-state-hover');

	if ($(this).children('ol').size() > 0){
	$(this).children('ol').hide();
	}
	});

	if ($(this).find('.ui-icon:first').size() > 0){
	$(this).find('.ui-icon:first').each(function (){
	$(this).css({
	position: 'absolute',
	right: 0,
	top: ($(this).parent().parent().parent().innerHeight() / 2) - ($(this).outerHeight(true) / 2)
	});
	});
	}

	<?php if ($WidgetProperties->forceFit == 'true'){ ?>
		if ($(this).hasClass('root')){
		Roots.push(this);
		}
		<?php } ?>
	});

	<?php if ($WidgetProperties->forceFit == 'true'){ ?>
		var numRoots = Roots.length;
		var totalWidth = $(Roots[0]).parent().parent().width();
		var RootsWidth = 0;
		$.each(Roots, function (i, el){
		RootsWidth += $(this).outerWidth(true);
		});

		var totalSpace = totalWidth - RootsWidth;
		var newPadding = (totalSpace / numRoots);
		$.each(Roots, function (i, el){
		$(this).css({
		width: $(this).innerWidth() + Math.floor(newPadding) + 'px'
		});
		});
		<?php } ?>
	});
		});
	<?php
 		$javascript = '/* Navigation Menu --BEGIN-- */' . "\n" .
			ob_get_contents();
		'/* Navigation Menu --END-- */' . "\n";
		ob_end_clean();

		return $javascript;
	}

	function loadLinkedSettings($WidgetProperties) {
		if (isset($WidgetProperties->linked_to)){
			$Qsettings = Doctrine_Query::create()
				->select('configuration_value')
				->from('TemplateManagerLayoutsWidgetsConfiguration')
				->where('configuration_key = ?', 'widget_settings')
				->andWhere('widget_id = ?', $WidgetProperties->linked_to)
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
			$LinkedProperties = json_decode($Qsettings[0]['configuration_value']);
			$WidgetProperties->menuSettings = $LinkedProperties->menuSettings;
		}
		return $WidgetProperties;
	}

	public function show() {
		$WidgetProperties = $this->loadLinkedSettings($this->getWidgetProperties());

		$menuItems = '';
		$this->firstAdded = false;
		if (isset($WidgetProperties->menuSettings)){
			//echo '<pre>';print_r($boxWidgetProperties['menuSettings']);
			$MenuSettings = array();
			foreach($WidgetProperties->menuSettings as $mInfo){
				if (isset($mInfo->link->condition) && $this->checkCondition($mInfo->link->condition) === true){
					$MenuSettings[] = $mInfo;
				}
			}

			foreach($MenuSettings as $k => $mInfo){
				$menuItems .= $this->parseMenuItem($mInfo, true, (!isset($MenuSettings[$k + 1])));
			}
		}

		$this->setBoxContent('<div id="' . $WidgetProperties->menuId . '" class="ui-navigation-menu ui-widget ui-corner-all"><ol>' . $menuItems . '</ol></div>');
		return $this->draw();
	}
}

?>
