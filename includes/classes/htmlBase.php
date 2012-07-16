<?php
require(sysConfig::getDirFsCatalog() . 'includes/classes/html/base/element.php');

/**
 * Static access for building html elements or widgets
 */
class htmlBase {

	public static function loadElement($elementType){
		$dummy = self::newElement($elementType);
		unset($dummy);
	}

	/**
	 * Initializes html element/widget based on the element type
	 * @param string $elementType The element type or widget name to use
	 * @param array $options [optional] Configuration settings to send to the element
	 * @return htmlElement
	 * @return htmlWidget
	 */
  	public static function newElement($elementType, $options = array()){
  		$elementClassName = 'htmlElement_' . $elementType;
  		$widgetClassName = 'htmlWidget_' . $elementType;
		$elementDir = sysConfig::getDirFsCatalog() . 'includes/classes/html/elements/';
		$widgetDir = sysConfig::getDirFsCatalog() . 'includes/classes/html/widgets/';

  		if (file_exists($elementDir . $elementType . '.php')){
 	 		if (!class_exists($elementClassName, false)){
 	 			require($elementDir . $elementType . '.php');
	  		}
	  		$element = new $elementClassName($options);
   		}elseif (file_exists($widgetDir . $elementType . '.php')){
 	 		if (!class_exists($widgetClassName, false)){
 	 			require($widgetDir . $elementType . '.php');
	  		}
	  		$element = new $widgetClassName($options);
  		}else{
  			$element = new htmlElement($elementType);
  		}
		return $element->startChain();
  	}

	/**
	 * @static
	 * @return htmlElement_input
	 */
	public static function newInput(){
		return self::newElement('input');
	}

	/**
	 * @static
	 * @return htmlWidget_checkbox
	 */
	public static function newCheckbox(){
		return self::newElement('checkbox');
	}

	/**
	 * @static
	 * @return htmlWidget_checkboxGroup
	 */
	public static function newCheckboxGroup(){
		return self::newElement('checkboxGroup');
	}

	/**
	 * @static
	 * @return htmlWidget_radio
	 */
	public static function newRadio(){
		return self::newElement('radio');
	}

	/**
	 * @static
	 * @return htmlWidget_radioGroup
	 */
	public static function newRadioGroup(){
		return self::newElement('radioGroup');
	}

	/**
	 * @static
	 * @return htmlElement_selectbox
	 */
	public static function newSelectbox(){
		return self::newElement('selectbox');
	}

	/**
	 * @static
	 * @return htmlElement_textarea
	 */
	public static function newTextarea(){
		return self::newElement('textarea');
	}

	/**
	 * @static
	 * @return htmlWidget_newGrid
	 */
	public static function newGrid(){
		return self::newElement('newGrid');
	}

	/**
	 * @static
	 * @return htmlElement_table
	 */
	public static function newTable(){
		return self::newElement('table');
	}

	/**
	 * @static
	 * @param string $type
	 * @return htmlElement_list
	 */
	public static function newList($type = 'ul'){
		return self::newElement('list')->setType($type == 'ul' ? 'unordered' : 'ordered');
	}

	/**
	 * @static
	 * @return htmlWidget_button
	 */
	public static function newButton(){
		return self::newElement('button');
	}

	/**
	 * @static
	 * @return htmlWidget_fileManager
	 */
	public static function newFileManager(){
		return self::newElement('fileManager');
	}

	/**
	 * @static
	 * @return htmlWidget_icon
	 */
	public static function newIcon(){
		return self::newElement('icon');
	}

	/**
	 * @static
	 * @return htmlElement_fieldset
	 */
	public static function newFieldset(){
		return self::newElement('fieldset');
	}

	/**
	 * @static
	 * @return htmlWidget_fieldsetFormBlock
	 */
	public static function newFieldsetFormBlock(){
		return self::newElement('fieldsetFormBlock');
	}

	/**
	 * @static
	 * @return htmlWidget_datePicker
	 */
	public static function newDatePicker(){
		return self::newElement('datePicker');
	}

	/**
	 * @static
	 * @return htmlWidget_actionWindow
	 */
	public static function newActionWindow(){
		return self::newElement('actionWindow');
	}

	/**
	 * @static
	 * @return htmlWidget_pageMenu
	 */
	public static function newPageMenu(){
		return self::newElement('pageMenu');
	}
	
	/**
	 * @static
	 * @return htmlWidget_ckEditor
	 */
	public static function newHtmlEditor(){
		return self::newElement('ck_editor');
	}
}
