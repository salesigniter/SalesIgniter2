<?php
ob_start();
if (!isset($WidgetSettings->linked_to)){
	$menuIcons = array(
		'none'   => '-- No Icon --',
		'jquery' => 'jQuery Icon',
		'custom' => 'My Own Icon'
	);

	$menuItemConditions = array(
		''                        => '-- No Condition --',
		'customer_logged_in'      => 'Customer is logged in',
		'customer_not_logged_in'  => 'Customer is not logged in',
		'shopping_cart_empty'     => 'Shopping cart is empty',
		'shopping_cart_not_empty' => 'Shopping cart is not empty'
	);

	$jqueryIcons = array('ui-icon-carat-1-n', 'ui-icon-carat-1-ne', 'ui-icon-carat-1-e', 'ui-icon-carat-1-se', 'ui-icon-carat-1-s', 'ui-icon-carat-1-sw', 'ui-icon-carat-1-w', 'ui-icon-carat-1-nw', 'ui-icon-carat-2-n-s', 'ui-icon-carat-2-e-w', 'ui-icon-triangle-1-n', 'ui-icon-triangle-1-ne', 'ui-icon-triangle-1-e', 'ui-icon-triangle-1-se', 'ui-icon-triangle-1-s', 'ui-icon-triangle-1-sw', 'ui-icon-triangle-1-w', 'ui-icon-triangle-1-nw', 'ui-icon-triangle-2-n-s', 'ui-icon-triangle-2-e-w', 'ui-icon-arrow-1-n', 'ui-icon-arrow-1-ne', 'ui-icon-arrow-1-e', 'ui-icon-arrow-1-se', 'ui-icon-arrow-1-s', 'ui-icon-arrow-1-sw', 'ui-icon-arrow-1-w', 'ui-icon-arrow-1-nw', 'ui-icon-arrow-2-n-s', 'ui-icon-arrow-2-ne-sw', 'ui-icon-arrow-2-e-w', 'ui-icon-arrow-2-se-nw', 'ui-icon-arrowstop-1-n', 'ui-icon-arrowstop-1-e', 'ui-icon-arrowstop-1-s', 'ui-icon-arrowstop-1-w', 'ui-icon-arrowthick-1-n', 'ui-icon-arrowthick-1-ne', 'ui-icon-arrowthick-1-e', 'ui-icon-arrowthick-1-se', 'ui-icon-arrowthick-1-s', 'ui-icon-arrowthick-1-sw', 'ui-icon-arrowthick-1-w', 'ui-icon-arrowthick-1-nw', 'ui-icon-arrowthick-2-n-s', 'ui-icon-arrowthick-2-ne-sw', 'ui-icon-arrowthick-2-e-w', 'ui-icon-arrowthick-2-se-nw', 'ui-icon-arrowthickstop-1-n', 'ui-icon-arrowthickstop-1-e', 'ui-icon-arrowthickstop-1-s', 'ui-icon-arrowthickstop-1-w', 'ui-icon-arrowreturnthick-1-w', 'ui-icon-arrowreturnthick-1-n', 'ui-icon-arrowreturnthick-1-e', 'ui-icon-arrowreturnthick-1-s', 'ui-icon-arrowreturn-1-w', 'ui-icon-arrowreturn-1-n', 'ui-icon-arrowreturn-1-e', 'ui-icon-arrowreturn-1-s', 'ui-icon-arrowrefresh-1-w', 'ui-icon-arrowrefresh-1-n', 'ui-icon-arrowrefresh-1-e', 'ui-icon-arrowrefresh-1-s', 'ui-icon-arrow-4', 'ui-icon-arrow-4-diag', 'ui-icon-extlink', 'ui-icon-newwin', 'ui-icon-refresh', 'ui-icon-shuffle', 'ui-icon-transfer-e-w', 'ui-icon-transferthick-e-w', 'ui-icon-folder-collapsed', 'ui-icon-folder-open', 'ui-icon-document', 'ui-icon-document-b', 'ui-icon-note', 'ui-icon-mail-closed', 'ui-icon-mail-open', 'ui-icon-suitcase', 'ui-icon-comment', 'ui-icon-person', 'ui-icon-print', 'ui-icon-trash', 'ui-icon-locked', 'ui-icon-unlocked', 'ui-icon-bookmark', 'ui-icon-tag', 'ui-icon-home', 'ui-icon-flag', 'ui-icon-calendar', 'ui-icon-cart', 'ui-icon-pencil', 'ui-icon-clock', 'ui-icon-disk', 'ui-icon-calculator', 'ui-icon-zoomin', 'ui-icon-zoomout', 'ui-icon-search', 'ui-icon-wrench', 'ui-icon-gear', 'ui-icon-heart', 'ui-icon-star', 'ui-icon-link', 'ui-icon-cancel', 'ui-icon-plus', 'ui-icon-plusthick', 'ui-icon-minus', 'ui-icon-minusthick', 'ui-icon-close', 'ui-icon-closethick', 'ui-icon-key', 'ui-icon-lightbulb', 'ui-icon-scissors', 'ui-icon-clipboard', 'ui-icon-copy', 'ui-icon-contact', 'ui-icon-image', 'ui-icon-video', 'ui-icon-script', 'ui-icon-alert', 'ui-icon-info', 'ui-icon-notice', 'ui-icon-help', 'ui-icon-check', 'ui-icon-bullet', 'ui-icon-radio-off', 'ui-icon-radio-on', 'ui-icon-pin-w', 'ui-icon-pin-s', 'ui-icon-play', 'ui-icon-pause', 'ui-icon-seek-next', 'ui-icon-seek-prev', 'ui-icon-seek-end', 'ui-icon-seek-start', 'ui-icon-seek-first', 'ui-icon-stop', 'ui-icon-eject', 'ui-icon-volume-off', 'ui-icon-volume-on', 'ui-icon-power', 'ui-icon-signal-diag', 'ui-icon-signal', 'ui-icon-battery-0', 'ui-icon-battery-1', 'ui-icon-battery-2', 'ui-icon-battery-3', 'ui-icon-circle-plus', 'ui-icon-circle-minus', 'ui-icon-circle-close', 'ui-icon-circle-triangle-e', 'ui-icon-circle-triangle-s', 'ui-icon-circle-triangle-w', 'ui-icon-circle-triangle-n', 'ui-icon-circle-arrow-e', 'ui-icon-circle-arrow-s', 'ui-icon-circle-arrow-w', 'ui-icon-circle-arrow-n', 'ui-icon-circle-zoomin', 'ui-icon-circle-zoomout', 'ui-icon-circle-check', 'ui-icon-circlesmall-plus', 'ui-icon-circlesmall-minus', 'ui-icon-circlesmall-close', 'ui-icon-squaresmall-plus', 'ui-icon-squaresmall-minus', 'ui-icon-squaresmall-close', 'ui-icon-grip-dotted-vertical', 'ui-icon-grip-dotted-horizontal', 'ui-icon-grip-solid-vertical', 'ui-icon-grip-solid-horizontal', 'ui-icon-gripsmall-diagonal-se', 'ui-icon-grip-diagonal-se');
	?>
<script src="<?php echo sysConfig::getDirWsCatalog();?>ext/jQuery/external/nestedSortable/jquery.ui.nestedSortable.js"></script>
<script src="<?php echo sysConfig::getDirWsCatalog();?>ext/jQuery/ui/jquery.ui.selectmenu.js"></script>
<link rel="stylesheet" href="<?php echo sysConfig::getDirWsCatalog();?>ext/jQuery/themes/smoothness/jquery.ui.selectmenu.css" type="text/css" media="screen,projection" />
<script type="text/javascript">
	var menuIcons = <?php echo json_encode($menuIcons);?>;
	var menuItemConditions = <?php echo json_encode($menuItemConditions);?>;
	var jqueryIcons = <?php echo json_encode($jqueryIcons);?>;

	$(document).ready(function () {
		$('#navMenuTable').find('ol.sortable').nestedSortable({
			disableNesting       : 'no-nest',
			forcePlaceholderSize : true,
			handle               : 'div',
			items                : 'li',
			opacity              : .6,
			placeholder          : 'placeholder',
			tabSize              : 25,
			tolerance            : 'pointer',
			toleranceElement     : '> div'
		});

		$('#navMenuTable').find('.addMainBlock').click(function () {
			var inputKey = 0;
			while($('#navMenuTable').find('.systemLinkType[data-input_key=' + inputKey + ']').size() > 0){
				inputKey++;
			}

			var menuIconOptions = '';
			$.each(menuIcons, function (k, v) {
				menuIconOptions += '<option value="' + k + '">' + v + '</option>';
			});

			var menuItemConditionsOptions = '';
			$.each(menuItemConditions, function (k, v) {
				menuItemConditionsOptions += '<option value="' + k + '">' + v + '</option>';
			});

			var $newLi = $('<li></li>').attr('id', 'menu_item_' + inputKey);
			$newLi.html('<div><table cellpadding="2" cellspacing="0" border="0">' +
				'<tr>' +
				'<td valign="top"><span>Show if (<select name="menu_item_link[' + inputKey + '][condition]">' + menuItemConditionsOptions + '</select>)</span></td>' +
				'<td valign="top"><select name="menu_item_link[' + inputKey + '][icon]" class="menuItemIcon">' + menuIconOptions + '</select></td>' +
				'<td valign="top"><table cellpadding="2" cellspacing="0" border="0">' +
				<?php
				foreach(sysLanguage::getLanguages() as $lInfo){
					echo '\'<tr>\' + ' . "\n" .
						'\'<td>' . $lInfo['showName']('&nbsp;') . '</td>\' + ' . "\n" .
						'\'<td><input type="text" name="menu_item_link[\' + inputKey + \'][text][' . $lInfo['id'] . ']" value="Menu Text"></td>\' + ' . "\n" .
						'\'</tr>\' + ' . "\n";
				}
				?>
				'</table></td>' +
				'<td valign="top" class="systemLinkMenuContainer"></td>' +
				'<td valign="top"><span class="ui-icon ui-icon-closethick menuItemDelete" tooltip="Delete Item and Children"></span></td>' +
				'</tr>' +
				'</table></div>');

			$.newSystemLinkMenu($newLi, 'menu_item_link[' + inputKey + ']');

			$('#navMenuTable').find('ol.sortable')
				.append($newLi);
		});

		$('.menuItemIcon').live('change', function () {
			var inputKey = $(this).parentsUntil('ol').last().attr('data-input_key');

			if ($(this).val() == 'jquery'){
				var options = '';
				$.each(jqueryIcons, function (k, v) {
					options = options + '<option value="' + k + '">' + v + '</option>';
				});
				var field = '<select name="menu_item_icon_src[' + inputKey + ']" class="menuItemIconSrc">' + options + '</select>';
			}
			else {
				if ($(this).val() == 'custom'){
					var field = '<input type="text" name="menu_item_icon_src[' + inputKey + ']" class="menuItemIconSrc BrowseServerField">';
				}
			}

			$(this).parent().find('.menuItemIconSrc').remove();
			$(field).insertAfter(this);

			if ($(this).val() == 'jquery'){
				$(this).parent().find('.menuItemIconSrc').selectmenu({
					style     : 'dropdown',
					width     : 60,
					menuWidth : 60,
					maxHeight : 300,
					format    : function (text) {
						return '<span class="ui-icon ' + text + '" style="position:relative;top:.5em;"></span>';
					}
				});
			}
		});

		$('.menuItemDelete').live('click', function () {
			$(this).parentsUntil('ol').last().remove();
		});

		$('select.menuItemIconSrc').selectmenu({
			style     : 'dropdown',
			width     : 60,
			menuWidth : 60,
			maxHeight : 300,
			format    : function (text) {
				return '<span class="ui-icon ' + text + '" style="position:relative;top:.5em;"></span>';
			}
		});

		$('.saveButton').click(function () {
			$('input[name=navMenuSortable]').val($('#navMenuTable').find('ol.sortable').nestedSortable('serialize'));
		});
	});
</script>
<style>
	.placeholder {
		background-color : #cfcfcf;
	}

	.ui-nestedSortable-error {
		background : #fbe3e4;
		color      : #8a1f11;
	}

	ol {
		margin       : 0;
		padding      : 0;
		padding-left : 30px;
	}

	ol.sortable, ol.sortable ol {
		margin          : 0 0 0 25px;
		padding         : 0;
		list-style-type : none;
	}

	ol.sortable {
		margin : 2em 0;
	}

	.sortable li {
		margin  : 7px 0 0 0;
		padding : 0;
	}

	.sortable li div {
		border  : 1px solid black;
		padding : 3px;
		margin  : 0;
		cursor  : move;
	}

	li .ui-icon-closethick {
		float  : right;
		margin : .5em;
	}

	li select {
		margin-left  : .5em;
		margin-right : .5em;
	}
</style>
<?php
}
$editTable = htmlBase::newElement('table')
	->setId('navMenuTable')
	->setCellPadding(2)
	->setCellSpacing(0);

$editTable->addBodyRow(array(
	'columns' => array(
		array('text' => '<b>Navigation Menu</b>')
	)
));

$editTable->addBodyRow(array(
	'columns' => array(
		array('text' => 'Id for css:<input type="text" name="menu_id" value="' . (isset($WidgetSettings->menuId) ? $WidgetSettings->menuId : '') . '">')
	)
));

$editTable->addBodyRow(array(
	'columns' => array(
		array('text' => '<input type="checkbox" name="force_fit" value="true"' . (isset($WidgetSettings->forceFit) && $WidgetSettings->forceFit == 'true' ? ' checked=checked' : '') . '> Expand To Fit Container')
	)
));

$LinkToSelect = htmlBase::newElement('selectbox')
	->setName('linked_to')
	->addOption('none', 'None')
	->selectOptionByValue((isset($WidgetSettings->linked_to) ? $WidgetSettings->linked_to : 'none'));

/*$QnavMenus = Doctrine_Query::create()
	->from('TemplateManagerLayoutsWidgets w')
	->leftJoin('w.TemplateManagerLayoutsColumns col')
	->leftJoin('col.TemplateManagerLayoutsContainers con')
	->leftJoin('con.TemplateManagerLayouts l')
	->leftJoin('l.TemplateManagerTemplates t')
	->where('identifier = ?', 'navigationMenu')
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);*/
$NavMenus = Doctrine_Core::getTable('TemplateManagerLayoutsWidgets')
	->findByIdentifier('navigationMenu');
if ($NavMenus && $NavMenus->count() > 0){
	foreach($NavMenus as $mInfo){
		$settings = json_decode($mInfo->Configuration['widget_settings']->configuration_value);
		$Container = $mInfo->Column->Container;
		while($Container->parent_id > 0){
			$Container = $Container->Parent;
		}
		$LinkToSelect->addOption(
			$mInfo->widget_id,
			$Container->Layout->Template->Configuration['NAME']->configuration_value .
				' >> ' .
				$Container->Layout->layout_name .
				' (' . $Container->Layout->layout_type . ')' .
				' >> ' .
				$settings->menuId
		);
	}
}

$editTable->addBodyRow(array(
	'columns' => array(
		array('text' => 'Link With Other Navigation Menu: <br>' . $LinkToSelect->draw())
	)
));

if (!isset($WidgetSettings->linked_to)){
	$editTable->addBodyRow(array(
		'columns' => array(
			array('text' => '<span class="ui-icon ui-icon-plusthick addMainBlock"></span><span class="ui-icon ui-icon-closethick"></span>')
		)
	));

	function parseMenuItem($item, &$i)
	{
		global $AppArray, $CatArr, $menuIcons, $menuLinkTypes, $jqueryIcons, $menuItemConditions, $menuLinkTargets, $template;

		$baseInputName = 'menu_item_link[' . $i . ']';
		$data = $item->link;

		$iconMenu = htmlBase::newElement('selectbox')
			->setName($baseInputName . '[icon]')
			->addClass('menuItemIcon')
			->selectOptionByValue((isset($data->icon) ? $data->icon : ''));
		foreach($menuIcons as $k => $v){
			$iconMenu->addOption($k, $v);
		}

		$iconInput = '';
		if ($data->icon == 'jquery'){
			$iconSrcMenu = htmlBase::newElement('selectbox')
				->setName($baseInputName . '[icon_src]')
				->addClass('menuItemIconSrc')
				->selectOptionByValue((isset($data->icon_src) ? $data->icon_src : 'none'));
			foreach($jqueryIcons as $v){
				$iconSrcMenu->addOption($v, $v);
			}
			$iconInput = $iconSrcMenu->draw();
		}
		elseif ($data->icon == 'custom') {
			$iconInput = htmlBase::newElement('input')
				->setName($baseInputName . '[icon_src]')
				->addClass('menuItemIconSrc')
				->addClass('BrowseServerField')
				->val($data->icon_src)
				->draw();
		}
		$textInputs = '<table cellpadding="2" cellspacing="0" border="0">';
		foreach(sysLanguage::getLanguages() as $lInfo){
			$textInput = htmlBase::newElement('input')
				->setName($baseInputName . '[text][' . $lInfo['id'] . ']')
				->val((isset($data->text->{$lInfo['id']}) ? $data->text->{$lInfo['id']} : ''));
			$textInputs .= '<tr>' .
				'<td>' . $lInfo['showName']('&nbsp;') . '</td>' .
				'<td>' . $textInput->draw() . '</td>' .
				'</tr>';
		}
		$textInputs .= '</table>';

		$linkConditionsMenu = htmlBase::newElement('selectbox')
			->setName($baseInputName . '[condition]')
			->addClass('menuItemCondition')
			->selectOptionByValue($data->condition);
		foreach($menuItemConditions as $k => $v){
			$linkConditionsMenu->addOption($k, $v);
		}

		$systemLinkMenu = htmlBase::newElement('systemLinkMenu', array(
			'data' => $data
		))->attr('data-input_key', $i)->setName($baseInputName);

		$itemTemplate = '<li id="menu_item_' . $i . '">' .
			'<div><table cellpadding="2" cellspacing="0" border="0" width="100%">' .
			'<tr>' .
			'<td valign="top"><span>Show if (' . $linkConditionsMenu->draw() . ')</span></td>' .
			'<td valign="top">' . $iconMenu->draw() . $iconInput . '</td>' .
			'<td valign="top">' . $textInputs . '</td>' .
			'<td valign="top">' . $systemLinkMenu->draw() . '</td>' .
			'<td valign="top"><span class="ui-icon ui-icon-closethick menuItemDelete" tooltip="Delete Item and Children"></span></td>' .
			'</tr>' .
			'</table>' .
			'</div>';

		$i++;
		if (!empty($data->children)){
			foreach($data->children as $childItem){
				$itemTemplate .= '<ol>' . parseMenuItem($childItem, &$i) . '</ol>';
			}
		}

		$itemTemplate .= '</li>';

		return $itemTemplate;
	}

	$menuItems = '';
	if (isset($WidgetSettings->menuSettings)){
		$i = 0;
		foreach($WidgetSettings->menuSettings as $mInfo){
			$menuItems .= parseMenuItem($mInfo, &$i);

			if (empty($mInfo->children)){
				$i++;
			}
		}
	}

	$editTable->addBodyRow(array(
		'columns' => array(
			array('text' => '<ol class="ui-widget sortable">' . $menuItems . '</ol>')
		)
	));
}
echo $editTable->draw();
echo '<input type="hidden" name="navMenuSortable" value="">';
if (isset($WidgetSettings->linked_to)){
	//echo '<input type="hidden" name="linked_to" value="' . $WidgetSettings->linked_to . '">';
}
$fileContent = ob_get_contents();
ob_end_clean();

$WidgetSettingsTable->addBodyRow(array(
	'columns' => array(
		array('colspan' => 2, 'text' => $fileContent)
	)
));
