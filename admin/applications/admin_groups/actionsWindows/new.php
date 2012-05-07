<?php
$AdminGroupsTable = Doctrine_Core::getTable('AdminGroups');
if (isset($_GET['group_id'])){
	$Group = $AdminGroupsTable->find((int)$_GET['group_id']);
}
else {
	$Group = $AdminGroupsTable->getRecord();
}

$infoBox = htmlBase::newElement('infobox');
$infoBox->setHeader('<b>' . ($Group->admin_groups_id > 0 ? sysLanguage::get('TEXT_ACTION_WINDOW_HEADING_EDIT') : sysLanguage::get('TEXT_ACTION_WINDOW_HEADING_NEW')) . '</b>');
$infoBox->setButtonBarLocation('top');

$saveButton = htmlBase::newElement('button')->addClass('saveButton')->usePreset('save');
$cancelButton = htmlBase::newElement('button')->addClass('cancelButton')->usePreset('cancel');

$infoBox->addButton($saveButton)->addButton($cancelButton);

$GroupNameInput = htmlBase::newElement('input')
	->setName('admin_groups_name')
	->setLabel(sysLanguage::get('TEXT_INFO_GROUPS_NAME'))
	->setLabelSeparator('<br />')
	->setLabelPosition('before')
	->val($Group->admin_groups_name);

$CustomerLoginInput = htmlBase::newElement('checkbox')
	->setName('customer_login')
	->setLabel(' Allowed to login as customer')
	->setLabelPosition('after')
	->val(1)
	->setChecked(($Group->customer_login_allowed == 1));

$infoBox->addContentRow($GroupNameInput->draw());
$infoBox->addContentRow($CustomerLoginInput->draw());

$Qpermissions = Doctrine_Query::create()
	->from('AdminApplicationsPermissions')
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
$perms = array();
foreach($Qpermissions as $pInfo){
	$permissions = explode(',', $pInfo['admin_groups']);
	if (!empty($pInfo['extension'])){
		$perms['ext'][$pInfo['extension']][$pInfo['application']][$pInfo['page']] = in_array($Group->admin_groups_id, $permissions);
	}
	else {
		$perms[$pInfo['application']][$pInfo['page']] = in_array($Group->admin_groups_id, $permissions);
	}
}

$Applications = new DirectoryIterator(sysConfig::getFirFsAdmin() . 'applications/');
$AppArray = array();
foreach($Applications as $AppDir){
	if ($AppDir->isDot() || $AppDir->isFile()) {
		continue;
	}
	$appName = $AppDir->getBasename();

	$AppArray[$appName] = array();

	if (is_dir($AppDir->getPathname() . '/pages/')){
		$Pages = new DirectoryIterator($AppDir->getPathname() . '/pages/');
		foreach($Pages as $Page){
			if ($Page->isDot() || $Page->isDir()) {
				continue;
			}
			$pageName = $Page->getBasename();

			$AppArray[$appName][$pageName] = (isset($perms[$appName][$pageName]) ? $perms[$appName][$pageName] : false);
		}
	}
	ksort($AppArray[$appName]);
}

$Extensions = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'extensions/');
foreach($Extensions as $Extension){
	if ($Extension->isDot() || $Extension->isFile()) {
		continue;
	}

	if (is_dir($Extension->getPathName() . '/admin/base_app/')){
		$extName = $Extension->getBasename();

		$AppArray['ext'][$extName] = array();

		$ExtApplications = new DirectoryIterator($Extension->getPathname() . '/admin/base_app/');
		$AppArray['ext'][$extName]['configure']['configure.php'] = (isset($perms['ext'][$extName]['configure']['configure.php']) ? $perms['ext'][$extName]['configure']['configure.php'] : false);
		foreach($ExtApplications as $ExtApplication){
			if ($ExtApplication->isDot() || $ExtApplication->isFile()) {
				continue;
			}
			$appName = $ExtApplication->getBasename();

			$AppArray['ext'][$extName][$appName] = array();

			if (is_dir($ExtApplication->getPathname() . '/pages/')){
				$ExtPages = new DirectoryIterator($ExtApplication->getPathname() . '/pages/');
				foreach($ExtPages as $ExtPage){
					if ($ExtPage->isDot() || $ExtPage->isDir()) {
						continue;
					}
					$pageName = $ExtPage->getBasename();

					$AppArray['ext'][$extName][$appName][$pageName] = (isset($perms['ext'][$extName][$appName][$pageName]) ? $perms['ext'][$extName][$appName][$pageName] : false);
				}
			}
			ksort($AppArray['ext'][$extName][$appName]);
		}
		ksort($AppArray['ext']);
	}
}

$Extensions = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'extensions/');
foreach($Extensions as $Extension){
	if ($Extension->isDot() || $Extension->isFile()) {
		continue;
	}

	if (is_dir($Extension->getPathName() . '/admin/ext_app/')){
		$ExtCheck = new DirectoryIterator($Extension->getPathname() . '/admin/ext_app/');
		foreach($ExtCheck as $eInfo){
			if ($eInfo->isDot() || $eInfo->isFile()) {
				continue;
			}

			if (is_dir($eInfo->getPathName() . '/pages')){
				$appName = $eInfo->getBasename();

				$Pages = new DirectoryIterator($eInfo->getPathname() . '/pages/');
				foreach($Pages as $Page){
					if ($Page->isDot() || $Page->isDir()) {
						continue;
					}
					$pageName = $Page->getBasename();

					if (!isset($AppArray[$appName][$pageName])){
						$AppArray[$appName][$pageName] = (isset($perms[$appName][$pageName]) ? $perms[$appName][$pageName] : false);
					}
				}
			}
			elseif (isset($AppArray['ext'][$eInfo->getBasename()])) {
				$Apps = new DirectoryIterator($eInfo->getPathName());
				$extName = $eInfo->getBasename();

				foreach($Apps as $App){
					if ($App->isDot() || $App->isFile()) {
						continue;
					}
					$appName = $App->getBasename();

					if (is_dir($App->getPathname() . '/pages')){
						$Pages = new DirectoryIterator($App->getPathname() . '/pages/');
						foreach($Pages as $Page){
							if ($Page->isDot() || $Page->isDir()) {
								continue;
							}
							$pageName = $Page->getBasename();

							if (!isset($AppArray['ext'][$extName][$App->getBasename()])){
								$AppArray['ext'][$extName][$App->getBasename()] = array();
							}

							$AppArray['ext'][$extName][$appName][$pageName] = (isset($perms['ext'][$extName][$appName][$pageName]) ? $perms['ext'][$extName][$appName][$pageName] : false);
						}
					}
				}
			}
		}
	}
}

ksort($AppArray);

//echo '<pre>';print_r($AppArray);

$BoxesTable = htmlBase::newElement('table')
	->setCellPadding(3)
	->setCellSpacing(0)
	->css(array('width' => '100%'));
$BoxesTable->addBodyRow(array(
	'columns' => array(
		array(
			'colspan' => 2,
			'text'    => '<input type="checkbox" id="checkAll"/> <span id="checkAllText">Check All Pages</span>'
		)
	)
));
$col = 0;
foreach($AppArray as $appName => $aInfo){
	if ($appName == 'ext') {
		continue;
	}

	if (!empty($aInfo)){
		$checkboxes = '<div class="ui-widget-header" style="margin: .5em;"><input type="checkbox" class="appBox checkAllPages"> ' . $appName . '</div>';
		foreach($aInfo as $pageName => $pageChecked){
			$checkboxes .= '<div style="margin: 0 0 0 1.5em;"><input class="pageBox" type="checkbox" name="applications[' . $appName . '][]" value="' . $pageName . '"' . ($pageChecked === true ? ' checked="checked"' : '') . '> ' . $pageName . '</div>';
		}
		$bodyCols[] = array(
			'valign' => 'top',
			'text'   => '<div class="ui-widget ui-widget-content">' . $checkboxes . '</div>'
		);

		$col++;
		if ($col > 2){
			$BoxesTable->addBodyRow(array(
				'columns' => $bodyCols
			));
			$bodyCols = array();
			$col = 0;
		}
	}
}

foreach($AppArray['ext'] as $ExtName => $eInfo){
	if (!empty($eInfo)){
		$checkboxes = '<div class="ui-widget-header" style="margin: .5em;"><input type="checkbox" class="extensionBox checkAllApps"> ' . $ExtName . '</div>';
		foreach($eInfo as $appName => $aInfo){
			$checkboxes .= '<div><div class="ui-state-hover" style="margin: .5em .5em 0 .5em"><input type="checkbox" class="appBox checkAllPages"> ' . $appName . '</div>';
			foreach($aInfo as $pageName => $pageChecked){
				$checkboxes .= '<div style="margin: 0 0 0 1.5em;"><input type="checkbox" class="pageBox" name="applications[ext][' . $ExtName . '][' . $appName . '][]" value="' . $pageName . '"' . ($pageChecked === true ? ' checked="checked"' : '') . '> ' . $pageName . '</div>';
			}
			$checkboxes .= '</div>';
		}
		$bodyCols[] = array(
			'valign' => 'top',
			'text'   => '<div class="ui-widget ui-widget-content">' . $checkboxes . '</div>'
		);

		$col++;
		if ($col > 2){
			$BoxesTable->addBodyRow(array(
				'columns' => $bodyCols
			));
			$bodyCols = array();
			$col = 0;
		}
	}
}

if (!empty($bodyCols)){
	$BoxesTable->addBodyRow(array(
		'columns' => $bodyCols
	));
}

EventManager::notify('AdminExtraPermissions', $BoxesTable, $Group->admin_groups_id);

$infoBox->addContentRow(sysLanguage::get('TEXT_INFO_GROUP_PERMISSIONS'));
$infoBox->addContentRow($BoxesTable->draw());

EventManager::notify('AdminGroupsNewEditWindowBeforeDraw', $infoBox, $Group);

EventManager::attachActionResponse($infoBox->draw(), 'html');
