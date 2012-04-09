<?php
    $beforeText = '';
	$accountEditLink = htmlBase::newElement('a')->html(sysLanguage::get('MY_ACCOUNT_INFORMATION'))
	->setHref(itw_app_link(null, 'account', 'edit', 'SSL'));

	$addressEditLink = htmlBase::newElement('a')->html(sysLanguage::get('MY_ACCOUNT_ADDRESS_BOOK'))
	->setHref(itw_app_link(null, 'account', 'address_book', 'SSL'));

	$passwordEditLink = htmlBase::newElement('a')->html(sysLanguage::get('MY_ACCOUNT_PASSWORD'))
	->setHref(itw_app_link(null, 'account', 'password', 'SSL'));

	$orderHistoryLink = htmlBase::newElement('a')->html(sysLanguage::get('MY_ORDERS_VIEW'))
	->setHref(itw_app_link(null, 'account', 'history', 'SSL'));
	if(sysConfig::get('ACCOUNT_NEWSLETTER') == 'true') {
		$newslettersLink = htmlBase::newElement('a')->html(sysLanguage::get('EMAIL_NOTIFICATIONS_NEWSLETTERS'))
		->setHref(itw_app_link(null, 'account', 'newsletters', 'SSL'));
	}

	$listIcon = '<span class="ui-icon ui-icon-carat-1-e" style="display:inline-block;"></span>';

	$Qorders = Doctrine_Query::create()
	->select('o.orders_id, o.date_purchased, oa.entry_name, oa.entry_country, ot.text as order_total, s.orders_status_id, sd.orders_status_name')
	->from('Orders o')
	->leftJoin('o.OrdersAddresses oa')
	->leftJoin('o.OrdersTotal ot')
	->leftJoin('o.OrdersStatus s')
	->leftJoin('s.OrdersStatusDescription sd')
	->where('o.customers_id = ?', $userAccount->getCustomerId())
	->andWhereIn('ot.module_type', array('total', 'ot_total'))
	->andWhere('sd.language_id = ?', Session::get('languages_id'))
	->andWhere('oa.address_type = ?', 'billing')
	->orderBy('o.orders_id desc')
	->limit('3');

    EventManager::notify('OrdersListingBeforeExecute', &$Qorders);

	$Result = $Qorders->execute(array(), Doctrine::HYDRATE_ARRAY);

	if ($Result){
		$ordersTable = htmlBase::newElement('table')->css('width', '100%')->setCellPadding(2)->setCellSpacing(0);
		foreach ($Result as $oInfo){
			$ordersTable->addBodyRow(array(
				'columns' => array(
					array('addCls' => 'main', 'text' => $oInfo['date_purchased']->format(sysLanguage::getDateFormat('short'))),
					array('addCls' => 'main', 'text' => '#' . $oInfo['orders_id']),
					array('addCls' => 'main', 'text' => $oInfo['OrdersAddresses'][0]['entry_name'] . ', ' . $oInfo['OrdersAddresses'][0]['entry_country']),
					array('addCls' => 'main', 'text' => $oInfo['OrdersStatus']['OrdersStatusDescription'][0]['orders_status_name'], 'align' => 'center'),
					array('addCls' => 'main', 'text' => $oInfo['order_total'], 'align' => 'right'),
					array('addCls' => 'main', 'text' => htmlBase::newElement('button')->setText(sysLanguage::get('TEXT_BUTTON_VIEW'))->setHref(itw_app_link('order_id=' . $oInfo['orders_id'], 'account', 'history_info', 'SSL'))->draw(), 'align' => 'right', 'css' => array('font-size' => '.8em'))
				)
			))->onClick('document.location.href=\'' . itw_app_link('order_id=' . $oInfo['orders_id'], 'account', 'history_info', 'SSL') . '\'');
		}
	}

	$pageTitle = sysLanguage::get('HEADING_TITLE_DEFAULT');
	
	$pageContents = '<div>' . 
		sprintf(sysLanguage::get('TEXT_LOGGED_IN_AS'), $userAccount->getFirstName(), itw_app_link(null, 'account', 'logoff')) . 
	'</div>'.'<div class="main" style="margin-top:1em;">' . $beforeText . '</div>';
	
	if (isset($ordersTable)){
		$pageContents .= '<div class="main" style="margin-top:1em;">' . 
			'<b>' . sysLanguage::get('OVERVIEW_TITLE') .'</b>' . 
			'<div style="float:right">' . 
				'<a href="' . itw_app_link(null, 'account', 'history', 'SSL'). '">' . 
					'<u>' . sysLanguage::get('OVERVIEW_SHOW_ALL_ORDERS') . '</u>' . 
				'</a>' . 
			'</div>' . 
		'</div>' . 
		'<div class="ui-widget ui-widget-content ui-corner-all" style="padding:1em;">' . 
			'<div class="main">' . 
				'<b>' . sysLanguage::get('OVERVIEW_PREVIOUS_ORDERS') . '</b>' . 
			'</div>' . 
			$ordersTable->draw() . 
		'</div>';
	}
	
	$links = '';

	$links .= sprintf('<li>%s%s</li>', $listIcon, $accountEditLink->draw()) . "\n";
	$links .= sprintf('<li>%s%s</li>', $listIcon, $addressEditLink->draw()) . "\n";
	$links .= sprintf('<li>%s%s</li>', $listIcon, $passwordEditLink->draw()) . "\n";

	$contents = EventManager::notifyWithReturn('AccountDefaultMyAccountAddLink');
	if (!empty($contents)){
		foreach($contents as $content){
			if(!is_array($content)){
				if (trim($content) != '') {
					$links .= sprintf('<li>%s%s</li>', $listIcon, $content) . "\n";
				}
			}
			else{
				foreach($content as $moreContent){
					if (trim($moreContent) != '') {
						$links .= sprintf('<li>%s%s</li>', $listIcon, $moreContent) . "\n";
					}
				}
			}
		}
	}

	/* remoteUpdate needs to do its job before to draw links */
	EventManager::notify('AccountDefaultMyAccountBeforeDrawLinks', &$links, &$rentalLinkList);

	$pageContents .= '<div class="main" style="margin-top:1em;">' . 
		'<b>' . sysLanguage::get('MY_ACCOUNT_TITLE') . '</b>' . 
	'</div>' . 
	'<div class="ui-widget ui-widget-content ui-corner-all" style="padding:1em;">' . 
		'<ul class="accountPageLinks">' .
			$links . 
		'</ul>' . 
	'</div>';
	
	EventManager::notify('AccountDefaultAddLinksBlock', &$pageContents);
	
	$pageContents .= '<div class="main" style="margin-top:1em;">' .
		'<b>' . sysLanguage::get('MY_ORDERS_TITLE') . '</b>' . 
	'</div>' . 
	'<div class="ui-widget ui-widget-content ui-corner-all" style="padding:1em;">' . 
		'<ul class="accountPageLinks">' .
			'<li>' . $listIcon . $orderHistoryLink->draw() . '</li>' . 
		'</ul>' . 
	'</div>';
	if(sysConfig::get('ACCOUNT_NEWSLETTER') == 'true') {
		$pageContents .= '<div class="main" style="margin-top:1em;">' .
			'<b>' . sysLanguage::get('EMAIL_NOTIFICATIONS_TITLE') . '</b>' .
		'</div>' .
		'<div class="ui-widget ui-widget-content ui-corner-all" style="padding:1em;">' .
			'<ul class="accountPageLinks">' .
				'<li>' . $listIcon . $newslettersLink->draw() . '</li>' .
			'</ul>' .
		'</div>';
	}
	$pageContent->set('pageTitle', $pageTitle);
	$pageContent->set('pageContent', $pageContents);
