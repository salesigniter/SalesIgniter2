<?php
if (($_GET['flag'] == '0') || ($_GET['flag'] == '1')){
	if (isset($_GET['product_id'])){
		tep_set_product_status($_GET['product_id'], $_GET['flag']);
		$messageStack->addSession('pageStack', 'Product status has been updated.', 'success');
	}
}
EventManager::attachActionResponse(itw_app_link(tep_get_all_get_params(array('action', 'flag'))), 'redirect');
?>