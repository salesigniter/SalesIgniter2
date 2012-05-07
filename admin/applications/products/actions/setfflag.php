<?php
if (($_GET['fflag'] == '0') || ($_GET['fflag'] == '1')){
	if (isset($_GET['product_id'])){
		tep_set_product_featured($_GET['product_id'], $_GET['fflag']);
		$messageStack->addSession('pageStack', 'Product featured status has been updated.', 'success');
	}
}

EventManager::attachActionResponse(itw_app_link(tep_get_all_get_params(array('action', 'fflag'))), 'redirect');
?>