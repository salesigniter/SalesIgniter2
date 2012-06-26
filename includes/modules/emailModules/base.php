<?php
class EmailModuleBase extends ModuleBase
{

	protected $_emailVars = array();

	protected $templateSubjectUnparsed = '';

	protected $templateBodyUnparsed = '';

	public function getEventSettings($eventKey, $currentSettings = array())
	{
		return false;
	}

	public function getEventVariables()
	{
		return array();
	}

	/**
	 * @param string $code
	 * @param bool   $forceEnable
	 * @param bool   $moduleDir
	 */
	public function init($code, $forceEnable = false, $moduleDir = false)
	{
		$this->import(new Installable);

		$this->setModuleType('email');
		parent::init($code, $forceEnable, $moduleDir);

		$this->_emailVars['today_short'] = date(sysLanguage::getDateFormat('short'));
		$this->_emailVars['today_long'] = date(sysLanguage::getDateFormat('long'));
		$this->_emailVars['store_name'] = sysConfig::get('STORE_NAME');
		$this->_emailVars['store_owner'] = sysConfig::get('STORE_OWNER');
		$this->_emailVars['store_owner_email'] = sysConfig::get('STORE_OWNER_EMAIL_ADDRESS');
		$this->_emailVars['store_url'] = sysConfig::get('HTTP_SERVER') . sysConfig::get('DIR_WS_CATALOG');
	}

	public function clearVars($clearGlobal = false)
	{
		if ($clearGlobal === true){
			foreach($this->_emailVars['global'] as $k => $v){
				$this->_emailVars['global'][$k] = '';
			}
			foreach($this->_emailVars['perEmail'] as $k => $v){
				$this->_emailVars['perEmail'][$k] = '';
			}
		}
		else {
			foreach($this->_emailVars['perEmail'] as $k => $v){
				$this->_emailVars['perEmail'][$k] = '';
			}
		}
	}

	public function setGlobalVar($k, $v)
	{
		$this->_emailVars['global'][$k] = $v;
	}

	public function setVar($k, $v)
	{
		$this->_emailVars['perEmail'][$k] = $v;
	}

	public function getGlobalVar($k)
	{
		return $this->_emailVars['global'][$k];
	}

	public function getVar($k)
	{
		return $this->_emailVars['perEmail'][$k];
	}

	public function setEmailSubject($val)
	{
		$this->templateSubjectUnparsed = $val;
	}

	public function setEmailBody($val)
	{
		$this->templateBodyUnparsed = explode("\n", $val);
	}

	public function parseTemplateSubject()
	{
		$subject = '';
		if (stristr($this->templateSubjectUnparsed, '{$')){
			$subject = $this->replaceVar($this->templateSubjectUnparsed);
		}
		else {
			$subject = $this->templateSubjectUnparsed;
		}
		return $subject;
	}

	public function parseTemplateBody($allowHTML = true)
	{
		$ifStarted = false;
		$curIfText = '';
		$templateText = '';
		foreach($this->templateBodyUnparsed as $line){
			if (substr($line, 0, 4) == '<!--'){
				$checkVarText = trim(str_replace(array('<!-- if', '(', ')', '$'), '', $line));
				$checkVar = (isset($this->_emailVars['perEmail'][$checkVarText]) ? $this->allowedVars['perEmail'][$checkVarText] : false);
				if ($checkVar === false){
					$checkVar = (isset($this->_emailVars['global'][$checkVarText]) ? $this->allowedVars['global'][$checkVarText] : false);
				}
				if ($checkVar){
					$ifSatisfied = true;
				}
				else {
					$ifSatisfied = false;
				}
				$ifStarted = true;
			}
			elseif ($ifStarted === true) {
				if (substr($line, 0, 3) == '-->'){
					$ifStarted = false;
					if ($ifSatisfied === true){
						$templateText .= $curIfText;
					}
					$curIfText = '';
				}
				else {
					if ($allowHTML == 0){
						$line = strip_tags($line);
					}
					if (stristr($line, '{$')){
						$curIfText .= $this->replaceVar($line);
					}
					else {
						$curIfText .= $line;
					}
				}
			}
			else {
				if ($allowHTML === false){
					$line = strip_tags($line);
				}
				if (stristr($line, '{$')){
					$templateText .= $this->replaceVar($line);
				}
				else {
					$templateText .= $line;
				}
			}
		}
		$templateText = str_replace('<--APPEND-->', '', $templateText);
		return $templateText;
	}

	public function replaceVar($string)
	{
		foreach($this->_emailVars['global'] as $varName => $val){
			$string = str_replace('{$' . $varName . '}', $val, $string);
		}
		foreach($this->_emailVars['perEmail'] as $varName => $val){
			$string = str_replace('{$' . $varName . '}', $val, $string);
		}
		if (stristr($string, '{$')){
			$newString = '';
			$erasing = false;
			for($i = 0, $n = strlen($string); $i < $n; $i++){
				if ($string[$i] == '{'){
					$erasing = true;
				}
				elseif ($erasing === true) {
					if ($string[$i] == '}'){
						$erasing = false;
					}
				}
				elseif ($erasing === false) {
					$newString .= $string[$i];
				}
			}
			$string = $newString;
		}
		return $string;
	}

	public function sendEmail($sendTo)
	{
		/*if (!empty($this->_emailVars)){
			if (isset($this->templateData[0]['EmailTemplatesDescription'][$this->languageId])){
				$emailInfo = $this->templateData[0]['EmailTemplatesDescription'][$this->languageId];

				if (is_null($this->templateFileParsed) === true){
					$this->templateFileUnparsed = $this->templateData[0]['email_templates_attach'];
					EventManager::notify('EmailEventPreParseTemplateFile_' . $this->eventName, &$this->templateFileUnparsed);
					$this->templateFileParsed = $this->parseTemplateFile();
				}
			}
		}*/

		$sendFrom = $this->_emailVars['store_owner'];
		$sendFromEmail = $this->_emailVars['store_owner_email'];

		if (isset($sendTo['from_email'])){
			$sendFromEmail = $sendTo['from_email'];
		}
		if (isset($sendTo['from_name'])){
			$sendFrom = $sendTo['from_name'];
		}

		if (isset($sendTo['attach'])){
			$this->templateFileParsed = $sendTo['attach'];
		}

		//echo 'tep_mail(' . $sendTo['name'] . ', ' . $sendTo['email'] . ', ' . $this->parseTemplateSubject() . ', ' . $this->parseTemplateBody() . ', ' . $sendFrom . ', ' . $sendFromEmail . ')';
		tep_mail(
			$sendTo['name'],
			$sendTo['email'],
			$this->parseTemplateSubject(),
			$this->parseTemplateBody(),
			$sendFrom,
			$sendFromEmail,
			$this->templateFileParsed
		);
	}
}
