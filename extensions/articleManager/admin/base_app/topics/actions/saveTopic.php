<?php
	$Topics = Doctrine_Core::getTable('Topics');
	if (isset($_GET['tID'])){
		$Topic = $Topics->find((int)$_GET['tID']);
	}else{
		$Topic = $Topics->create();
		if (isset($_GET['parent_id'])){
			$Topic->parent_id = $_GET['parent_id'];
		}
	}
	
	$Topic->sort_order = (int)$_POST['sort_order'];
	
	$TopicsDescription =& $Topic->TopicsDescription;
foreach (sysLanguage::getLanguages() as $lInfo) {
		$lID = $lInfo['id'];

		$TopicsDescription[$lID]->language_id = $lID;
		$TopicsDescription[$lID]->topics_name = $_POST['topics_name'][$lID];
		$TopicsDescription[$lID]->topics_heading_title = $_POST['topics_heading_title'][$lID];
		$TopicsDescription[$lID]->topics_description = $_POST['topics_description'][$lID];
	}

	$Topic->save();

	EventManager::attachActionResponse(itw_app_link(tep_get_all_get_params(array('action', 'tID', 'parent_id')) . 'tID=' . $Topic->topics_id, 'topics', 'default'), 'redirect');
?>