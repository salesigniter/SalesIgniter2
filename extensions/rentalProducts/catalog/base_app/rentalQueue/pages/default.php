<?php
/*
	SalesIgniter E-Commerce System v1

	I.T. Web Experts
	http://www.itwebexperts.com

	Copyright (c) 2010 I.T. Web Experts

	This script and it's source is not redistributable
*/

$pageContents = '';
$RentalQueue = &Session::getReference('RentalQueue');

$Qshipped = Doctrine_Query::create()
	->from('RentedQueue r')
	->where('r.return_date <= ?', '0000-00-00 00:00:00')
	->andWhere('r.customers_id = ?', $userAccount->getCustomerId())
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

$info_box_contents = array();
$info_box_contents[0][] = array(
	'align'  => 'left',
	'params' => 'class="ui-widget-header"',
	'text'   => sysLanguage::get('TABLE_HEADING_TITLE')
);

$info_box_contents[0][] = array(
	'align'  => 'left',
	'params' => 'class="ui-widget-header"',
	'text'   => sysLanguage::get('TABLE_HEADING_SHIPMENT_DATE')
);

$info_box_contents[0][] = array(
	'align'  => 'left',
	'params' => 'class="ui-widget-header"',
	'text'   => sysLanguage::get('TABLE_HEADING_ARRIVAL_DATE')
);

$i = 1;
if ($Qshipped){
	foreach($Qshipped as $sInfo){
		if ($i % 2 == 1){
			$info_box_contents[] = array('params' => 'class="productListing-even"');
		}
		else {
			$info_box_contents[] = array('params' => 'class="productListing-odd"');
		}

		$productsName = '<a target="_blank" href="' . itw_app_link('products_id=' . $sInfo['products_id'], 'product', 'info') . '">' . tep_get_products_name($sInfo['products_id']) . '</a>';
		$shipDate = $sInfo['shipment_date'];
		$shipDateString = date("m/d/Y", strtotime($shipDate));
		$arrivalDate = $sInfo['arrival_date'];
		$arrivalDateString = date("m/d/Y", strtotime($arrivalDate));

		$info_box_contents[$i][] = array(
			'params' => 'class="productListing-data"',
			'text'   => $productsName
		);

		$info_box_contents[$i][] = array(
			'align'  => 'left',
			'params' => 'class="productListing-data" valign="top"',
			'text'   => $shipDateString
		);

		$info_box_contents[$i][] = array(
			'align'  => 'left',
			'params' => 'class="productListing-data" valign="top"',
			'text'   => $arrivalDateString
		);
		$i++;
	}
}
$shippedProducts = $i - 1;

if ($shippedProducts){
	ob_start();
	echo '<div><b>' . sysLanguage::get('TEXT_SHIPPED_PRODUCTS') . ' - ' . $shippedProducts . '</b><br><br>';
	new productListingBox($info_box_contents);
	echo '</div><br>';
	$pageContents .= ob_get_contents();
	ob_end_clean();
}

if ($RentalQueue->hasContents() === true){
	ob_start();
	echo '<div style="margin:1em;"><b>' . sysLanguage::get('TEXT_REQUESTED_PRODUCTS') . " - " . $RentalQueue->countContents() . '</b></div>';

	$QueueTable = htmlBase::newElement('table')
		->setCellPadding(2)
		->setCellSpacing(0)
		->css(array(
		'width' => '100%'
		))
		->stripeRows('productListing-even', 'productListing-odd');

	$HeaderRows = array(
		array('addCls' => 'ui-widget-header', 'text' => sysLanguage::get('TABLE_HEADING_PRIORITY')),
		array('addCls' => 'ui-widget-header', 'text' => sysLanguage::get('TABLE_HEADING_TITLE'))
	);

	if (sysConfig::get('RENTAL_AVAILABILITY_RENTAL_QUEUE') == 'true'){
		$HeaderRows[] = array('addCls' => 'ui-widget-header', 'text' => sysLanguage::get('TABLE_HEADING_AVAILABILITY'));
	}

	EventManager::notify('ListingRentalQueueHeader', &$HeaderRows);

	$HeaderRows[] = array('addCls' => 'ui-widget-header', 'text' => sysLanguage::get('TABLE_HEADING_REMOVE'));

	$QueueTable->addHeaderRow(array(
		'columns' => $HeaderRows
	));

	//	$RentalQueue = new RentalQueue();
	$QueueContents = $RentalQueue->getProducts()->getIterator();
	while($QueueContents->valid()){
		$QueueProduct = $QueueContents->current();

		$queueProductId = $QueueProduct->getId();
		$productId = $QueueProduct->getData('product_id');

		$PurchaseType = $QueueProduct
			->getProductClass()
			->getProductTypeClass()
			->getPurchaseType('membershipRental');

		$QproductsInQueue = Doctrine_Query::create()
			->select('count(*) as total')
			->from('CustomersQueue')
			->where('queue_data LIKE ?', '%\"product_id\";i:' . $productId . ';%')
			->andWhere('customers_id != ?', $userAccount->getCustomerId())
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

		$QAvailability = Doctrine_Query::create()
			->from('RentalAvailability r')
			->leftJoin('r.RentalAvailabilityDescription rad')
			->where('rad.language_id = ?', Session::get('languages_id'))
			->orderBy('ratio')
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

		$availability = $QproductsInQueue[0]['total'] - $PurchaseType->getCurrentStock();
		$availabilityName = null;

		if ($QAvailability){
			foreach($QAvailability as $aInfo){
				if ($availability <= $aInfo['ratio']){
					$availabilityName = $aInfo['RentalAvailabilityDescription'][0]['name'];
					break;
				}
			}
		}

		$BodyRowCols = array(
			array(
				'align' => 'center',
				'text' => htmlBase::newElement('input')
					->setName('queue_priority[' . $queueProductId . ']')
					->setSize(2)
					->setValue($QueueProduct->getPriority())
					->draw()
			),
			array(
				'align' => 'center',
				'text' => $QueueProduct->getNameHtml()
			)
		);

		if (sysConfig::get('RENTAL_AVAILABILITY_RENTAL_QUEUE') == 'true'){
			$BodyRowCols[] = array(
				'align' => 'center',
				'text' => $availabilityName
			);
		}

		EventManager::notify('ListingRentalQueue', &$BodyRowCols, $QueueProduct);

		$BodyRowCols[] = array(
			'align' => 'center',
			'text' => htmlBase::newElement('checkbox')
				->setName('queue_delete[]')
				->setValue($queueProductId)
				->draw()
		);

		$QueueTable->addBodyRow(array(
			'columns' => $BodyRowCols
		));

		$QueueContents->next();
	}

	echo $QueueTable->draw();

	$pageContents .= ob_get_contents();
	ob_end_clean();

	$pageContents = htmlBase::newElement('form')
		->setAction(itw_app_link('appExt=rentalProducts&action=updateQueue', 'rentalQueue', 'default'))
		->setName('update_rental_queue')
		->setMethod('post')
		->html($pageContents)
		->draw();

	$link = itw_app_link(null, 'products', 'all');
	if (isset($navigation->snapshot['get']) && sizeof($navigation->snapshot['get']) > 0){
		if (isset($navigation->snapshot['get']['cPath'])){
			$link = itw_app_link('cPath=' . $navigation->snapshot['get']['cPath'], 'index', 'default');
		}
	}

	$continueButtonHtml = htmlBase::newElement('button')
		->setName('continue')
		->setText(sysLanguage::get('TEXT_BUTTON_CONTINUE_CART'))
		->setHref($link);

	$updateQueueButton = htmlBase::newElement('button')
		->setText(sysLanguage::get('TEXT_BUTTON_UPDATE_QUEUE'))
		->setType('submit');

	$pageButtons = $continueButtonHtml->draw() . ' ' . $updateQueueButton->draw();
}
else {
	$pageContents .= sysLanguage::get('TEXT_QUEUE_EMPTY');

	$pageButtons = htmlBase::newElement('button')
		->usePreset('continue')
		->setHref(itw_app_link(null, 'index', 'default'))
		->draw();
}

$pageContent->set('pageTitle', sysLanguage::get('HEADING_TITLE'));
$pageContent->set('pageContent', $pageContents);
$pageContent->set('pageButtons', $pageButtons);
?>