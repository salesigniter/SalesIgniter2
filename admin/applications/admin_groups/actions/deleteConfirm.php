<?php
$success = false;
$toDelete = explode(',', $_GET['group_id']);
$AdminGroups = Doctrine_Core::getTable('AdminGroups');
foreach($toDelete as $groupId){
	$AdminGroup = $AdminGroups->find((int) $groupId);
	if ($AdminGroup){
		$Permissions = Doctrine_Core::getTable('AdminApplicationsPermissions');
		$groupId = $AdminGroup->admin_groups_id;

		$Reset = $Permissions->findAll();
		foreach($Reset as $rInfo){
			$perms = explode(',', $rInfo->admin_groups);
			if (in_array($groupId, $perms)){
				foreach($perms as $idx => $id){
					if ($id == $groupId){
						unset($perms[$idx]);
						break;
					}
				}
				$rInfo->admin_groups = implode(',', $perms);
				$rInfo->save();
			}
		}
		$AdminGroup->delete();
		$success = true;
	}
}

EventManager::attachActionResponse(array(
	'success' => $success
), 'json');
