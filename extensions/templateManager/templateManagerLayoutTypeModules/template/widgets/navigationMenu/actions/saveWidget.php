<?php
if (!isset($_POST['linked_to']) || $_POST['linked_to'] == 'none'){
	$menuConfig = array();
	if (!empty($_POST['navMenuSortable'])){
		parse_str($_POST['navMenuSortable'], $items);
		$i = 0;
		foreach($items['menu_item'] as $itemId => $parent){
			if ($parent == 'root'){
				$menuItem = $_POST['menu_item_link'][$itemId];

				$menuConfig[$i] = array(
					'link'     => $menuItem,
					'children' => array()
				);

				if (in_array($itemId, $items['menu_item'])){
					//parseChildren($itemId, $items['menu_item'], $menuConfig[$i]['children']);
				}
				$i++;
			}
		}
	}
	$WidgetProperties['menuSettings'] = $menuConfig;
}
else {
	$WidgetProperties['linked_to'] = $_POST['linked_to'];
}

$WidgetProperties['menuId'] = $_POST['menu_id'];
$WidgetProperties['forceFit'] = (isset($_POST['force_fit']) ? 'true' : 'false');
