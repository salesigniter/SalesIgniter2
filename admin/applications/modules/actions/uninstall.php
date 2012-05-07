<?php
$Installer = new ModuleInstaller($_GET['moduleType'], $_GET['module'], (isset($_GET['extName']) ? $_GET['extName'] : null));
$Installer->remove();

EventManager::attachActionResponse(itw_app_link('moduleType=' . $_GET['moduleType'], 'modules', 'default'), 'redirect');
