<?php
$Response = array(
	'success' => false
);

$admin_groups_name = ucwords(strtolower($_POST['admin_groups_name']));

$error = false;
if (empty($_POST['admin_groups_name']) || strlen($_POST['admin_groups_name']) <= 5){
	$messageStack->addSession('pageStack', sysLanguage::get('TEXT_INFO_GROUPS_NAME_FALSE'), 'error');
	$error = true;
}

$AdminGroups = Doctrine_Core::getTable('AdminGroups');
if ($error === false){
	$searchGroups = Doctrine_Query::create()
		->select('admin_groups_name')
		->from('AdminGroups')
		->where('admin_groups_name = ?', $admin_groups_name);
	if (isset($_GET['group_id'])){
		$searchGroups->andWhere('admin_groups_id != ?', (int)$_GET['group_id']);
	}
	$searchGroups->execute();
	if ($searchGroups->count() > 0){
		$messageStack->addSession('pageStack', sysLanguage::get('TEXT_INFO_GROUPS_NAME_USED'), 'error');
		$error = true;
	}
}

if ($error === false){
	if (isset($_GET['group_id'])){
		$AdminGroup = $AdminGroups->findOneByAdminGroupsId((int)$_GET['group_id']);
	}
	else {
		$AdminGroup = $AdminGroups->getRecord();
	}
	$AdminGroup->admin_groups_name = $admin_groups_name;
	$AdminGroup->customer_login_allowed = (isset($_POST['customer_login']) ? '1' : '0');
	$AdminGroup->save();

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

	if (isset($_POST['applications'])){
		foreach($_POST['applications'] as $appName => $Pages){
			if ($appName == 'ext') {
				continue;
			}

			foreach($Pages as $pageName){
				$Permission = $Permissions->findOneByApplicationAndPage($appName, $pageName);
				if (!$Permission){
					$Permission = new AdminApplicationsPermissions();
					$Permission->application = $appName;
					$Permission->page = $pageName;
				}

				$currentGroups = array();
				if (strlen($Permission->admin_groups) > 0){
					$currentGroups = explode(',', $Permission->admin_groups);
				}

				if (!in_array($groupId, $currentGroups)){
					$currentGroups[] = $groupId;
				}
				$Permission->admin_groups = implode(',', $currentGroups);
				$Permission->save();
			}
		}
	}

	if (isset($_POST['applications']['ext'])){
		foreach($_POST['applications']['ext'] as $extName => $Applications){
			foreach($Applications as $appName => $Pages){
				foreach($Pages as $pageName){
					$Permission = $Permissions->findOneByApplicationAndPageAndExtension($appName, $pageName, $extName);
					if (!$Permission){
						$Permission = new AdminApplicationsPermissions();
						$Permission->application = $appName;
						$Permission->page = $pageName;
						$Permission->extension = $extName;
					}

					$currentGroups = array();
					if (strlen($Permission->admin_groups) > 0){
						$currentGroups = explode(',', $Permission->admin_groups);
					}

					if (!in_array($groupId, $currentGroups)){
						$currentGroups[] = $groupId;
					}
					$Permission->admin_groups = implode(',', $currentGroups);
					$Permission->save();
				}
			}
		}
	}

	$Response['success'] = true;
}else{
	$Response['error'] = array(
		'message' => $messageStack->output('pageStack')
	);
}

EventManager::attachActionResponse($Response, 'json');
