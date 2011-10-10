<?php

class MetaTagsProductClassImport extends MI_Importable {

	private $metaInfo = array();

	public function getHeadTitle($langId = false){
		return $this->metaInfo['head_title'][$this->getLanguageId($langId)];
	}

	public function getHeadDesc($langId = false){
		return $this->metaInfo['head_desc'][$this->getLanguageId($langId)];
	}

	public function getHeadKeywords($langId = false){
		return $this->metaInfo['head_keywords'][$this->getLanguageId($langId)];
	}

	public function setHeadTitle($val, $langId = false){
		$this->metaInfo['head_title'][$this->getLanguageId($langId)] = $val;
	}

	public function setHeadDesc($val, $langId = false){
		$this->metaInfo['head_desc'][$this->getLanguageId($langId)] = $val;
	}

	public function setHeadKeywords($val, $langId = false){
		$this->metaInfo['head_keywords'][$this->getLanguageId($langId)] = $val;
	}
}

/**
 * @brief Handle Meta Tags
 *
 * @details
 * Add Meta tags into html header
 *
 * @author Erick Romero
 * @version 1
 *
 * I.T. Web Experts, Rental Store v2
 * http://www.itwebexperts.com
 * Copyright (c) 2009 I.T. Web Experts
 * This script and it's source is not redistributable
 */

class metaTags_admin_products_new_product extends Extension_metaTags {

	/**
	 * constructor
	 * @public
	 * @return void
	 */
	public function __construct(){
		parent::__construct();
	}

	// -------------------------------------------------------------------------------------------

	/**
	 * Loaded by core (extensions)
	 * Define the events to listen to
	 * @public
	 * @return void
	 */
	public function load(){
		if ($this->enabled === false) return;

		EventManager::attachEvents(array(
			'ProductsFormMiddle',
			'ProductsDescriptionsBeforeSave',
			'ProductInfoClassConstruct'
		), null, $this);
	}

	public function ProductInfoClassConstruct(&$ProductClass, $Product){
		$ProductClass->import(new MetaTagsProductClassImport);

		foreach($Product->ProductsDescription->toArray() as $dInfo){
			$ProductClass->setHeadTitle($dInfo['products_head_title_tag'], $dInfo['language_id']);
			$ProductClass->setHeadDesc($dInfo['products_head_desc_tag'], $dInfo['language_id']);
			$ProductClass->setHeadKeywords($dInfo['products_head_keywords_tag'], $dInfo['language_id']);
		}
	}

	// -------------------------------------------------------------------------------------------

	/**
	 * listen for ProductsFormMiddle event (fired by core)
	 * creates and set the form's elements to add meta tags (title, description and keyword)
	 *
	 * @public
	 * @param	langid	(int)	the language id
	 * @param	content	(array)	variable to store the form's elements
	 * @return string
	 */
	public function ProductsFormMiddle($langid, &$content, $Product) {

		$values = false;

		//post found? it means that the info was posted but some validation stopped the process
		//get post values instead of stored ones
		if (isset($_POST['metatags'])) {
			$values = array(
				't' => $_POST['metatags'][$langid]['t'],
				'd' => $_POST['metatags'][$langid]['d'],
				'k' => $_POST['metatags'][$langid]['k']
			);
		}
		else {
			$values = array(
				't' => $Product->getHeadTitle($langid),
				'd' => $Product->getHeadDesc($langid),
				'k' => $Product->getHeadKeywords($langid)
			);
		}

		$elements = $this->createFormElements($langid, $values);
		$content[] = array(
			'label' => sysLanguage::get('HEADER_META_TITLE'),
			'content' => $elements['t']->draw()
		);
		$content[] = array(
			'label' => sysLanguage::get('HEADER_META_DESC'),
			'content' => $elements['d']->draw()
		);
		$content[] = array(
			'label' => sysLanguage::get('HEADER_META_KEYWORD'),
			'content' => $elements['k']->draw()
		);
	}

	// -------------------------------------------------------------------------------------------

	/**
	 * listen for ProductsDescriptionsBeforeSave event (fired by core)
	 * add the meta varialbes to description from $_POST
	 *
	 * @public
	 * @param	$descriptions	(array)	variable to store the metatags
	 * @return string
	 */
	public function ProductsDescriptionsBeforeSave($descriptions) {

		if (isset($_POST['metatags'])) {
			foreach ($_POST['metatags'] as $langid => $vals) {
				$descriptions[$langid]->products_head_title_tag		= $vals['t'];
				$descriptions[$langid]->products_head_desc_tag 		= $vals['d'];
				$descriptions[$langid]->products_head_keywords_tag	= $vals['k'];
			}
		}
	}

}

?>
