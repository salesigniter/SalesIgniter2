<?php
$pageTitle = 'Select Closest Store';

$input = htmlBase::newElement('input')
	->setName('postcode');

$mapsHolder = htmlBase::newElement('div')
	->setId('mapsHolder')
	->html('<div id="map_canvas" style="width: 600px; height: 300px"></div><table><tbody></tbody></table>');

$pageContents = '<script src="http://maps.google.com/maps/api/js?sensor=true" type="text/javascript"></script>' .
	'<p>Enter Your Address:' .
	$input->draw() .
	htmlBase::newElement('button')->setId('findStores')->setText('Find Closest')->draw() .
	'<br>( Format: Street Address, Zipcode )</p>' .
	$mapsHolder->draw();
$pageButtons = htmlBase::newElement('button')->setId('setStore')->setText('Reserve Product At Store')->hide()->draw();

$pageContent->set('pageTitle', $pageTitle);
$pageContent->set('pageContent', $pageContents);
$pageContent->set('pageButtons', $pageButtons);
