<?php
/*
	Customers Custom Fields Version 1

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class Extension_customersCustomFields extends ExtensionBase
{

	public function __construct() {
		parent::__construct('customersCustomFields');
	}

	public function init() {
		global $App, $appExtension, $Template;
		if ($this->isEnabled() === false) {
			return;
		}

		EventManager::attachEvents(array(
			'CustomerQueryBeforeExecute',
			'CustomerInfoAddTableContainer'
		), null, $this);

		if ($appExtension->isAdmin()){
			EventManager::attachEvent('BoxCustomersAddLink', null, $this);
		}
	}

	public function CustomerInfoAddTableContainer(&$customer) {
		//Add tabs for custom fields:

		$Query = $this->_getFieldsQuery();
		$groups = $Query->execute()->toArray(true);

		$return = '';
		$groups_content = array();
		foreach($groups as $groupInfo){
			$fieldsToGroups = $groupInfo['CustomersCustomFieldsToGroups'];
			foreach($fieldsToGroups as $fieldToGroup){
				if (!empty($fieldToGroup['CustomersCustomFields']['CustomersCustomFieldsToCustomers'][0]['value'])){
					$name = $fieldToGroup['CustomersCustomFields']['CustomersCustomFieldsDescription'][Session::get('languages_id')]['field_name'];
					$return .= '<fieldset>
					<legend></legend>
					</fieldset>';
					if (Session::get('layoutType') == 'smartphone'){
						$return .= '<li><a href="#" data-href="tab' . $name . '">' . $name . '</a></li>';
					}
					else {
						$return .= '<li><a href="#tab' . $name . '"><span>' . $name . '</span></a></li>';
					}
					$groups_content['tab' . $name] = $fieldToGroup['CustomersCustomFields']['CustomersCustomFieldsToProducts'][0]['value'];
				}
			}
		}
		$product->custom_tabs_content = $groups_content;

		return $return;
	}

	public function BoxCustomersAddLink(&$contents) {
		$contents['children'][] = array(
			'link'	   => itw_app_link('appExt=customersCustomFields', 'manage', 'default', 'SSL'),
			'text'	   => 'Custom Fields'
		);
	}

	public function CustomerQueryBeforeExecute(&$productQuery) {
		$productQuery->addSelect('f2c.field_id')
			->leftJoin('c.CustomersCustomFieldsToCustomers f2c');
	}

	protected function _getFieldsQuery($settings = array()) {
		$Query = Doctrine_Query::create()
			->select('g.group_name, f.*, fd.field_name, f2p.value, f2g.sort_order')
			->from('ProductsCustomFieldsGroups g')
			->leftJoin('g.ProductsCustomFieldsGroupsToProducts g2p')
			->leftJoin('g.ProductsCustomFieldsToGroups f2g')
			->leftJoin('f2g.ProductsCustomFields f')
			->leftJoin('f.ProductsCustomFieldsDescription fd')
			->leftJoin('f.ProductsCustomFieldsToProducts f2p')
			->where('fd.field_name is not null')
			->orderBy('f2g.sort_order');

		if (!empty($settings['product_id'])){
			$Query->andWhere('f2p.product_id = ?', (int)$settings['product_id'])
				->andWhere('g2p.product_id = ?', (int)$settings['product_id']);
		}
		else {
			$Query->andWhere('f.include_in_search = ?', '1')
				->addGroupBy('f2p.value');
		}

		if (!empty($settings['group_id'])){
			$Query->andWhere('g.group_id = ?', (int)$settings['group_id']);
		}

		if (isset($settings['show_on_site']) && $settings['show_on_site'] === true){
			$Query->andWhere('f.show_on_site = 1');
		}

		if (isset($settings['show_on_tab']) && $settings['show_on_tab'] === true){
			$Query->andWhere('f.show_on_tab = 1');
		}

		if (isset($settings['show_on_labels']) && $settings['show_on_labels'] === true){
			$Query->andWhere('f.show_on_labels = 1');
		}

		if (isset($settings['show_on_listing']) && $settings['show_on_listing'] === true){
			$Query->andWhere('f.show_on_listing = 1');
		}

		if (!empty($settings['language_id'])){
			$Query->andWhere('fd.language_id = ?', (int)$settings['language_id']);
		}

		return $Query;
	}

	public function getFields($pId = null, $languageId = null, $shownOnProductInfo = false, $shownOnLabels = false, $shownOnListing = false, $groupId = null) {
		$Query = $this->_getFieldsQuery(array(
			'product_id'      => $pId,
			'group_id'        => $groupId,
			'language_id'     => $languageId,
			'show_on_site'    => $shownOnProductInfo,
			'show_on_labels'  => $shownOnLabels,
			'show_on_listing' => $shownOnListing
		));

		$Result = $Query->execute()->toArray(true);

		return $Result;
	}
}

?>