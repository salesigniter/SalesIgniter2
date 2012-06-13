<?php
$contents = array(
	'text' => 'Accounting',
	'link' => false,
	'children' => array()
);

if (
	sysPermissions::adminAccessAllowed('accounts_receivable') === true
){
	$subChildren = array();

	AccountsReceivableModules::loadModules();
	foreach(AccountsReceivableModules::getModules() as $Module){
		if ($Module->isEnabled() === true){
			if (sysPermissions::adminAccessAllowed('accounts_receivable', 'sales') === true){
				$subChildren[] = array(
					'link' => itw_app_link('sale_module=' . $Module->getCode(), 'accounts_receivable', 'sales', 'SSL'),
					'text' => $Module->getTitle() . 's'
				);
			}
		}
	}

	/*if (sysPermissions::adminAccessAllowed('accounts_receivable', 'sales') === true){
		$subChildren[] = array(
			'link' => itw_app_link(null, 'accounts_receivable', 'sales', 'SSL'),
			'text' => 'View All'
		);
	}*/

	$contents['children'][] = array(
		'text'	 => 'Accounts Receivable',
		'link'	 => false,
		'children' => $subChildren
	);
}
