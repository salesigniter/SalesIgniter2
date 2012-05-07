<?php
$Qcustomers = Doctrine_Query::create()
	->from('Customers c')
	->leftJoin('c.CustomersMembership cm')
	->leftJoin('c.MembershipBillingReport mu on (mu.customers_id = c.customers_id and mu.date = "' . sysConfig::get('LAST_CRON_DATE') . '")')
	->leftJoin('c.CustomersInfo i')
	->leftJoin('c.AddressBook a on (c.customers_id = a.customers_id and c.customers_default_address_id = a.address_book_id)')
	->leftJoin('a.Countries co');

if (isset($_GET['select_newletter'])){
	$Qcustomers->andWhere('customers_newsletter = ?', '1');
}

EventManager::notify('CustomersListingQueryBeforeExecute', $Qcustomers);

$tableGrid = htmlBase::newElement('newGrid')
	->useSearching(true)
	->useSorting(true)
	->usePagination(true)
	->useCsvExport(true)
	->setCsvFields($ExportModule->getSupportedColumns())
	->setMainDataKey('customer_id')
	->allowMultipleRowSelect(true)
	->setQuery($Qcustomers);

$gridButtons = array(
	htmlBase::newElement('button')->usePreset('new')->addClass('newButton'),
	htmlBase::newElement('button')->usePreset('edit')->addClass('editButton')->disable(),
	htmlBase::newElement('button')->usePreset('delete')->addClass('deleteButton')->disable(),
	htmlBase::newElement('button')->usePreset('orders')->addClass('ordersButton')->disable(),
	htmlBase::newElement('button')->usePreset('email')->addClass('emailButton')->disable()
);

if (Session::exists('customer_login_allowed') && Session::get('customer_login_allowed') === true){
	$gridButtons[] = htmlBase::newElement('button')
		->usePreset('login')
		->setText(sysLanguage::get('LOGIN_AS_CUSTOMER'))
		->setTooltip(sysLanguage::get('LOGIN_AS_CUSTOMER'))
		->addClass('loginAsCustomerButton')
		->disable();
}

EventManager::notify('AdminCustomersGridAddButtons', &$gridButtons);

$tableGrid->addButtons($gridButtons);

$searchForm = htmlBase::newElement('form')
	->attr('name', 'search')
	->attr('id', 'search')
	->attr('action', itw_app_link(null, null, null, 'SSL'))
	->attr('method', 'get');

$selectNewsLetter = htmlBase::newElement('checkbox')
	->setName('select_newletter')
	->setId('selectNewsLetter')
	->setLabel('Select NewsLetter')
	->setLabelPosition('after');

if (isset($_GET['select_newletter'])){
	$selectNewsLetter->setChecked(true);
}

$searchTable = htmlBase::newElement('table')
	->setCellPadding(3)
	->setCellSpacing(0)
	->attr('align', 'center');

$bodyCols = array(
	array('text' => $selectNewsLetter),
	array('text' => htmlBase::newElement('button')->setType('submit')->usePreset('search'))
);

if (isset($_GET['search'])){
	$resetButton = htmlBase::newElement('button')
		->setText(sysLanguage::get('TEXT_BUTTON_RESET'))
		->setHref(itw_app_link(null, null, 'default'));

	$bodyCols[] = array('text' => $resetButton);
}

$searchTable->addBodyRow(array(
	'columns' => $bodyCols
));
$searchForm->append($searchTable);

$tableGrid->addBeforeButtonBar($searchForm->draw());

$tableGridHeader = array(
	array(
		'text'      => sysLanguage::get('TABLE_HEADING_CUSTOMERS_ID'),
		'useSort'   => true,
		'sortKey'   => 'c.customers_id',
		'useSearch' => true,
		'searchObj' => GridSearchObj::Equal()
			->useFieldObj(htmlBase::newElement('input')->setName('search_customer_id'))
			->setDatabaseColumn('c.customers_id')
	),
	array(
		'text'      => sysLanguage::get('TABLE_HEADING_EMAIL_ADDRESS'),
		'useSort'   => true,
		'sortKey'   => 'c.customers_email_address',
		'useSearch' => true,
		'searchObj' => GridSearchObj::Like()
			->useFieldObj(htmlBase::newElement('input')->setName('search_customer_email'))
			->setDatabaseColumn('c.customers_email_address')
	),
	array(
		'text'      => sysLanguage::get('TABLE_HEADING_LASTNAME'),
		'useSort'   => true,
		'sortKey'   => 'c.customers_lastname',
		'useSearch' => true,
		'searchObj' => GridSearchObj::Like()
			->useFieldObj(htmlBase::newElement('input')->setName('search_customer_lastname'))
			->setDatabaseColumn('c.customers_lastname')
	),
	array(
		'text'      => sysLanguage::get('TABLE_HEADING_FIRSTNAME'),
		'useSort'   => true,
		'sortKey'   => 'c.customers_firstname',
		'useSearch' => true,
		'searchObj' => GridSearchObj::Like()
			->useFieldObj(htmlBase::newElement('input')->setName('search_customer_firstname'))
			->setDatabaseColumn('c.customers_firstname')
	),
	array(
		'text'    => sysLanguage::get('TABLE_HEADING_ACCOUNT_CREATED'),
		'useSort' => true,
		'sortKey' => 'i.customers_info_date_account_created'
	)
);

EventManager::notify('AdminCustomerListingAddHeader', &$tableGridHeader);

$tableGridHeader[] = array('text' => sysLanguage::get('TABLE_HEADING_INFO'));
$tableGrid->addHeaderRow(array(
	'columns' => $tableGridHeader
));

$customers = &$tableGrid->getResults();
if ($customers){
	foreach($customers as $customer){
		$customerId = $customer['customers_id'];

		$Qorders = Doctrine_Query::create()
			->select('count(*) as total')
			->from('Orders o')
			->where('o.customers_id = ?', $customerId);

		EventManager::notify('OrdersListingBeforeExecute', &$Qorders);

		$Qorders = $Qorders->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

		$tableGridBodyRow = array(
			'rowAttr' => array(
				'data-customer_id'    => $customerId,
				'data-customer_email' => $customer['customers_email_address'],
				'data-has_customers'  => ($Qorders[0]['total'] > 0 ? 'true' : 'false'),
				'data-has_orders'     => ($Qorders[0]['total'] > 0 ? 'true' : 'false')
			),
			'columns' => array(
				array('text' => $customer['customers_id']),
				array('text' => $customer['customers_email_address']),
				array('text' => $customer['customers_lastname']),
				array('text' => $customer['customers_firstname']),
				array(
					'text'  => $customer['CustomersInfo']['customers_info_date_account_created']->format(sysLanguage::getDateFormat('long')),
					'align' => 'center'
				)
			)
		);

		EventManager::notify('AdminCustomerListingAddBodyRow', $customer, &$tableGridBodyRow);

		/* Deprecated Please Update Scripts*/
		EventManager::notify('AdminCustomerListingAddBody', $customer, &$tableGridBodyRow['columns']);

		$tableGridBodyRow['columns'][] = array(
			'text'  => htmlBase::newElement('icon')->setType('info'),
			'align' => 'center'
		);

		$tableGrid->addBodyRow($tableGridBodyRow);

		$tableGrid->addBodyRow(array(
			'addCls'  => 'gridInfoRow',
			'columns' => array(
				array(
					'colspan' => sizeof($tableGridBodyRow['columns']),
					'text'    => '<table cellpadding="1" cellspacing="0" border="0" width="75%">' .
						'<tr>' .
						'<td><b>' . sysLanguage::get('TEXT_DATE_ACCOUNT_CREATED') . '</b></td>' .
						'<td> ' . $customer['CustomersInfo']['customers_info_date_account_created']->format(sysLanguage::getDateFormat('long')) . '</td>' .
						'<td><b>' . sysLanguage::get('TEXT_DATE_ACCOUNT_LAST_MODIFIED') . '</b></td>' .
						'<td>' . $customer['CustomersInfo']['customers_info_date_account_last_modified']->format(sysLanguage::getDateFormat('long')) . '</td>' .
						'<td></td>' .
						'</tr>' .
						'<tr>' .
						'<td><b>' . sysLanguage::get('TEXT_INFO_DATE_LAST_LOGON') . '</b></td>' .
						'<td>' . $customer['CustomersInfo']['customers_info_date_of_last_logon']->format(sysLanguage::getDateFormat('long')) . '</td>' .
						'<td><b>' . sysLanguage::get('TEXT_INFO_NUMBER_OF_LOGONS') . '</b></td>' .
						'<td>' . $customer['CustomersInfo']['customers_info_number_of_logons'] . '</td>' .
						'<td></td>' .
						'</tr>' .
						'<tr>' .
						'<td><b>' . sysLanguage::get('TEXT_INFO_COUNTRY') . '</b></td>' .
						'<td>' . $customer['AddressBook'][0]['Countries']['countries_name'] . '</td>' .
						'</tr>' .
						'</table>'
				)
			)
		));
	}
}

$array_filter = array(
	array(
		'id'   => '',
		'text' => sysLanguage::get('TEXT_ALL')
	),
	array(
		'id'   => 'M',
		'text' => sysLanguage::get('TEXT_MEMBERS')
	),
	array(
		'id'   => 'U',
		'text' => sysLanguage::get('TEXT_NON_MEMBERS')
	)
);
?>
<table cellspacing="0" cellpadding="0" style="width:99%;margin-right:5px;margin-left:5px;">
	<tr>
		<td>
			<table width="100%" cellspacing="0" cellpadding="2">
				<tr>
					<td class="dataTableRowD">&nbsp;&nbsp;&nbsp;</td>
					<td class="smallText"><?php echo sysLanguage::get('TEXT_INFO_RECUR_DENIED');?></td>
				</tr>
				<tr>
					<td class="dataTableRowA">&nbsp;&nbsp;&nbsp;</td>
					<td class="smallText"><?php echo sysLanguage::get('TEXT_INFO_RECUR_SUCCESS');?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<div class="ui-widget ui-widget-content ui-corner-all" style="margin-right:5px;margin-left:5px;">
	<div style="margin:5px;"><?php echo $tableGrid->draw();?></div>
</div>
<?php
EventManager::notify('AdminCustomersAfterTableDraw');
