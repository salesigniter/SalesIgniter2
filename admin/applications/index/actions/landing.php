<?php
function makeLandingMenu($item) {
	$return = '<ul>';
	foreach($item['children'] as $cInfo){
		$return .= '<li>';
		if (!empty($cInfo['children'])){
			$return .= '<div style="margin:1em 0;">' .
				'<div class="landingMenuBlockHeading">' . $cInfo['text'] . '</div>' .
				makeLandingMenu($cInfo) .
				'</div>';
		}
		else {
			$return .= '<a href="' . $cInfo['link'] . '">' . $cInfo['text'] . '</a>';
		}
		$return .= '</li>';
	}
	$return .= '</ul>';
	return $return;
}

function makeLandingDashboard($item) {
	if (!empty($item['children'])){
		$return = '<div class="ui-widget ui-widget-content ui-corner-all landingMenuBlock">' .
			'<div class="landingMenuBlockHeading">' . $item['text'] . '</div>' .
			'<div class="landingMenuBlockMenu">' .
			makeLandingMenu($item) .
			'</div>' .
			'</div>';
	}
	else {
		$return = '<div class="ui-widget ui-widget-content ui-corner-all landingMenuBlock">' .
			'<div class="landingMenuBlockDirectLink">' .
			'<a href="' . $item['link'] . '">' . $item['text'] . '</a>' .
			'</div>' .
			'</div>';
	}
	return $return;
}

echo '<span class="ui-icon ui-icon-window-close removeLanding" style="position:absolute;right:.5em;top:.5em;"></span>';
require(sysConfig::getDirFsAdmin() . 'includes/boxes/' . $_GET['box'] . '.php');
foreach($contents['children'] as $cInfo){
	echo makeLandingDashboard($cInfo);
}
EventManager::attachActionResponse('', 'html');