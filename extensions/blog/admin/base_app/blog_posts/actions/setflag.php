<?php
	if ( ($_GET['flag'] == '0') || ($_GET['flag'] == '1') ) {
		if (isset($_GET['pID'])) {
			tep_set_post_status($_GET['pID'], $_GET['flag']);
			$messageStack->addSession('pageStack', 'Post status has been updated.', 'success');
		}
	}
	EventManager::attachActionResponse(itw_app_link(tep_get_all_get_params(array('action', 'flag'))), 'redirect');
?>