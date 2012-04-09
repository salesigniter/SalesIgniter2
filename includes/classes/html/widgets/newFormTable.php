<?php
/**
 * Form Table Widget Class
 * @package Html
 */
class htmlWidget_newFormTable implements htmlWidgetPlugin {

	protected $tableElement;

	protected $template;

	protected $fieldKey = null;

	protected $formFields = array();

	protected $models = array();

	public function __construct(){
		$this->tableElement = htmlBase::newElement('table')
			->setCellPadding(2)
			->setCellSpacing(0);
	}

	public function __call($function, $args){
		$return = call_user_func_array(array($this->tableElement, $function), $args);
		if (!is_object($return)){
			return $return;
		}
		return $this;
	}

	/* Required Classes From Interface: htmlElementPlugin --BEGIN-- */
	public function startChain(){
		return $this;
	}

	public function setId($val){
		$this->tableElement->attr('id', $val);
		return $this;
	}

	public function setName($val){
		$this->tableElement->attr('name', $val);
		return $this;
	}

	public function draw(){
		global $App;
		if ($App->getAppName() == 'mobile'){
			$Element = htmlBase::newElement('div');
			foreach($this->formFields as $Field){
				$FieldHtmlObj = $Field->getField();
				if (is_null($this->fieldKey) === false){
					$FieldHtmlObj->setName($this->fieldKey . '[' . $Field->getName() . ']');
				}
				$Element->append(htmlBase::newElement('div')->html($Field->getLabel()));
				$Element->append($FieldHtmlObj);
			}

			return $Element->draw();
		}else{
			foreach($this->formFields as $Field){
				$FieldHtmlObj = $Field->getField();
				if (is_null($this->fieldKey) === false){
					$FieldHtmlObj->setName($this->fieldKey . '[' . $Field->getName() . ']');
				}
				$this->addRow(
					$Field->getLabel(),
					$FieldHtmlObj->draw()
				);
			}

			return $this->tableElement->draw();
		}
	}
	/* Required Classes From Interface: htmlElementPlugin --END-- */

	public function setAddClass($class){
		$this->addClass = $class;
		return $this;
	}

	public function addRow($leftCol, $rightCol = false){
		global $App;

		$colArr = array();
		$colArr[0] = array(
			'text' => $leftCol
		);

		if ($rightCol !== false){
			$colArr[1] = array(
				'text' => $rightCol
			);
		}else{
			if ($App->getAppName() != 'mobile'){
				$colArr[0]['colspan'] = '2';
			}
		}

		foreach($colArr as $idx => $col){
			$colArr[$idx]['valign'] = 'top';
			$colArr[$idx]['align'] = 'left';

			if (isset($this->addClass)){
				$colArr[$idx]['addCls'] = $this->addClass;
			}
		}

		$this->tableElement->addBodyRow(array(
			'columns' => $colArr
		));
	}

	public function setTemplate($template){
		$this->template = $template;
	}

	public function setFieldKey($val){
		$this->fieldKey = $val;
		return $this;
	}

	public function loadForm($key){
		$formFields = array();

		$Qform = Doctrine_Query::create()
			->from('FormManager m')
			->leftJoin('m.FormManagerFields f')
			->where('form_key = ?', $key)
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		foreach($Qform[0]['FormManagerFields'] as $fInfo){
			$formFields[] = $fInfo;
		}

		usort($formFields, function ($a, $b){
			return ($a['field_display_order'] > $b['field_display_order'] ? 1 : -1);
		});

		$this->formFields = array();
		foreach($formFields as $fInfo){
			$className = 'formTableField' . ucfirst($fInfo['field_module']);
			if (!class_exists($className)){
				require(sysConfig::getDirFsCatalog() . 'includes/classes/html/widgets/formTable/' . $fInfo['field_module'] . '.php');
			}
			$Field = new $className;
			$Field->setRequired($fInfo['field_required']);
			if ($fInfo['field_minlength'] > -1){
				$Field->setMinLength($fInfo['field_minlength']);
			}
			if ($fInfo['field_maxlength'] > -1){
				$Field->setMaxLength($fInfo['field_maxlength']);
			}

			$this->formFields[$fInfo['field_module']] = $Field;
		}
		return $this;
	}

	public function setFieldReadOnly($name, $val){
		foreach($this->formFields as $k => $Field){
			if ($Field->getName() == $name){
				$this->formFields[$k]->setReadOnly($val);
				break;
			}
		}
		return $this;
	}

	public function setFieldValue($name, $val){
		foreach($this->formFields as $k => $Field){
			if ($Field->getName() == $name){
				$this->formFields[$k]->setValue($val);
				break;
			}
		}
		return $this;
	}

	public function setFieldValues($valueArr){
		foreach($valueArr as $key => $val){
			foreach($this->formFields as $k => $Field){
				if ($Field->getName() == $key){
					$this->formFields[$k]->setValue($val);
				}
			}
		}
		return $this;
	}

	public function removeField($name){
		foreach($this->formFields as $k => $Field){
			if ($Field->getName() == $name){
				unset($this->formFields[$k]);
				break;
			}
		}
		return $this;
	}

	public function getField($name){
		foreach($this->formFields as $k => $Field){
			if ($Field->getName() == $name){
				return $this->formFields[$k];
				break;
			}
		}
		return null;
	}

	public function addModel(&$Model){
		$this->models[get_class($Model)] = $Model;
	}

	public function validate($Data){
		$valid = true;
		foreach($this->formFields as $Field){
			if (isset($Data[$Field->getName()])){
				$valid = $Field->validate($Data[$Field->getName()]);
				if ($valid !== false){
					$Field->setValue($valid);
				}
			}
		}
		return ($valid === false ? false : true);
	}

	public function processData(){
		foreach($this->formFields as $Field){
			if ($Field->hasDatabaseMappings()){
				foreach($Field->getDatabaseMappings() as $mapInfo){
					if (isset($this->models[$mapInfo['model_name']])){
						$this->models[$mapInfo['model_name']]->{$mapInfo['model_field']} = $Field->getValue();
					}
				}
			}
		}
	}
}

class formTableField {

	protected $name;

	protected $required;

	protected $minLength = -1;

	protected $maxLength = -1;

	protected $label;

	protected $value;

	protected $readOnly = 'false';

	protected $tooltip;

	private $mappings;

	public function setName($val){
		$this->name = $val;
	}

	public function setRequired($val = 'true'){
		$this->required = $val;
	}

	public function setMinLength($val){
		$this->minLength = $val;
	}

	public function setMaxLength($val){
		$this->maxLength = $val;
	}

	public function setLabel($val){
		$this->label = $val;
	}

	public function setValue($val){
		$this->value = $val;
	}

	public function setTooltip($val){
		$this->tooltip = $val;
	}

	public function setReadOnly($val){
		$this->readOnly = ($val === true ? 'true' : 'false');
	}

	public function getName(){
		return $this->name;
	}

	public function getRequired(){
		return $this->required;
	}

	public function getMinLength(){
		return $this->minLength;
	}

	public function getMaxLength(){
		return $this->maxLength;
	}

	public function getLabel(){
		return $this->label;
	}

	public function getValue(){
		return $this->value;
	}

	public function getTooltip(){
		return $this->tooltip;
	}

	public function getReadOnly(){
		return $this->readOnly;
	}

	public function getField($type){
		$field = htmlBase::newElement($type)
			->setName($this->getName());

		if ($this->getRequired() == 'true'){
			$field->attr('required', $this->getRequired());
		}

		if ($this->getReadOnly() == 'true'){
			$field->attr('read_only', $this->getReadOnly());
		}

		if ($this->getTooltip() != ''){
			$field->attr('tooltip', $this->getTooltip());
		}

		if ($this->getMinLength() > -1){
			$field->attr('minlength', $this->getMinLength());
		}

		if ($this->getMaxLength() > -1){
			$field->attr('maxlength', $this->getMaxLength());
		}

		if ($type == 'selectbox'){
			$field->selectOptionByValue($this->getValue());
		}elseif ($type == 'checkbox' || $type == 'radio'){
			$field->setChecked($this->getValue());
		}else{
			$field->val($this->getValue());
		}

		return $field;
	}

	public function setDatabaseMappings($Mappings){
		$this->mappings = $Mappings;
	}

	public function getDatabaseMappings(){
		return $this->mappings;
	}

	public function hasDatabaseMappings(){
		return (sizeof($this->mappings) > 0);
	}

	public function validate($val){
		global $messageStack;
		$validated = true;
		if (
			($this->getMinLength() > -1 && strlen($val) < $this->getMinLength()) ||
			($this->getMaxLength() > -1 && strlen($val) > $this->getMaxLength()) ||
			($this->getRequired() == 'true' && $val == '')
		){
			$validated = false;
			$messageStack->addSession('pageStack', $this->getLabel() . ' Did Not Pass Validation.', 'error');
		}

		return ($validated === true ? $val : false);
	}
}
