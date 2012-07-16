<?php
if (sysPermissions::adminAccessAllowed('extensions', 'default') === true){
	$extensionPages = array();
	$sorted = array();
	foreach($appExtension->getExtensions() as $extCls){
		$sorted[$extCls->getExtensionKey()] = $extCls;
	}
	ksort($sorted);

	$k = 0;
	foreach($sorted as $classObj){
		$k++;
		$pages  = array();
		if (sysPermissions::adminAccessAllowed('configure', 'configure', $classObj->getExtensionKey()) === true){
			$pages = array(
				array(
					'link' => itw_app_link('action=edit&ext=' . $classObj->getExtensionKey(), 'extensions', 'default', 'SSL'),
					'text' => 'Configure'
				)
			);
		}

		if (is_dir($classObj->getExtensionDir() . 'admin/base_app/')){
			$extDir = new DirectoryIterator($classObj->getExtensionDir() . 'admin/base_app/');
			foreach($extDir as $extFileObj){
				if ($extFileObj->isDot() === true || $extFileObj->isDir() === false) {
					continue;
				}
				if (file_exists($extFileObj->getPath() . '/' . $extFileObj->getBaseName() . '/.menu_ignore')) {
					continue;
				}

				if (file_exists($extFileObj->getPath() . '/' . $extFileObj->getBaseName() . '/pages/default.php')){
					if (sysPermissions::adminAccessAllowed($extFileObj->getBaseName(), 'default', $classObj->getExtensionKey()) === true){
						$pages[] = array(
							'link' => itw_app_link('appExt=' . $classObj->getExtensionKey(), $extFileObj->getBaseName(), 'default', 'SSL'),
							'text' => ucwords(str_replace('_', ' ', $extFileObj->getBaseName()))
						);
					}
				}
			}
		}

		if (is_dir(sysConfig::getDirFsCatalog() . 'clientData/extensions/' . $classObj->getExtensionKey() . '/admin/base_app')){
			$extDir = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'clientData/extensions/' . $classObj->getExtensionKey() . '/admin/base_app/');
			foreach($extDir as $extFileObj){
				if ($extFileObj->isDot() === true || $extFileObj->isDir() === false) {
					continue;
				}
				if (file_exists($extFileObj->getPath() . '/' . $extFileObj->getBaseName() . '/.menu_ignore')) {
					continue;
				}

				if (file_exists($extFileObj->getPath() . '/' . $extFileObj->getBaseName() . '/pages/default.php')){
					if (sysPermissions::adminAccessAllowed($extFileObj->getBaseName(), 'default', $classObj->getExtensionKey()) === true){
						$pages[] = array(
							'link' => itw_app_link('appExt=' . $classObj->getExtensionKey(), $extFileObj->getBaseName(), 'default', 'SSL'),
							'text' => ucwords(str_replace('_', ' ', $extFileObj->getBaseName()))
						);
					}
				}
			}
		}

		if(count($pages) > 0){
			$contents['children'][] = array(
				'link' => itw_app_link('ext=' . $classObj->getExtensionKey(), 'extensions', 'default', 'SSL'),
				'text' => $classObj->getExtensionName(),
				'children' => $pages
			);
		}
	}
}
