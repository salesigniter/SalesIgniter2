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

		$this->setGlobalVar('today_short', date(sysLanguage::getDateFormat('short')));
		$this->setGlobalVar('today_long', date(sysLanguage::getDateFormat('long')));
		$this->setGlobalVar('store_name', sysConfig::get('STORE_NAME'));
		$this->setGlobalVar('store_owner', sysConfig::get('STORE_OWNER'));
		$this->setGlobalVar('store_owner_email', sysConfig::get('STORE_OWNER_EMAIL_ADDRESS'));
		$this->setGlobalVar('store_url', sysConfig::get('HTTP_SERVER') . sysConfig::get('DIR_WS_CATALOG'));
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

	public function getGlobalVars()
	{
		return $this->_emailVars['global'];
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
		$this->templateBodyUnparsed = $val;
	}

	public function parseTemplateSubject()
	{
		$subject = '';
		if (stristr($this->templateSubjectUnparsed, '$')){
			$subject = $this->replaceVar($this->templateSubjectUnparsed);
		}
		else {
			$subject = $this->templateSubjectUnparsed;
		}
		return $subject;
	}

	public function parseTemplateBody($allowHTML = true)
	{
		$conditional = array();
		$Parsed = $this->templateBodyUnparsed;
		preg_match_all('/\[if \$([a-z0-9_]+)\](?|<br[ \/]*>|)[\s]*(.*)\[endif\](?|<br[ \/]*>|)[\s]*/imsU', $Parsed, &$conditional);
		if (sizeof($conditional) > 0){
			foreach($conditional[1] as $k => $condition){
				$checkVar = (isset($this->_emailVars['perEmail'][$condition]) ? $this->_emailVars['perEmail'][$condition] : false);
				if ($checkVar === false){
					$checkVar = (isset($this->_emailVars['global'][$condition]) ? $this->_emailVars['global'][$condition] : false);
				}

				if ($checkVar){
					$Parsed = str_replace($conditional[0][$k], $conditional[2][$k], $Parsed);
				}else{
					$Parsed = str_replace($conditional[0][$k], '', $Parsed);
				}
			}
		}

		$Parsed = $this->replaceVar($Parsed);
		return $Parsed;
	}

	public function prepareValForRegex($val){
		return str_replace(
			array('$'),
			array('\$'),
			$val
		);
	}

	public function replaceVar($string)
	{
		foreach($this->_emailVars['global'] as $varName => $val){
			$string = preg_replace('/\$' . $varName . '/', $this->prepareValForRegex($val), $string, 1);
		}
		foreach($this->_emailVars['perEmail'] as $varName => $val){
			$string = preg_replace('/\$' . $varName . '/', $this->prepareValForRegex($val), $string, 1);
		}
		if (stristr($string, '$')){
			//$string = preg_replace('/\$[a-z0-9_]/i', '', $string);
		}
		$string = str_replace(array('\n', '\r'), '', $string);
		return $string;
	}

	public function sendEmail($sendTo)
	{
		$sendFrom = $this->getGlobalVar('store_owner');
		$sendFromEmail = $this->getGlobalVar('store_owner_email');

		if (isset($sendTo['from_email'])){
			$sendFromEmail = $sendTo['from_email'];
		}
		if (isset($sendTo['from_name'])){
			$sendFrom = $sendTo['from_name'];
		}

		if (class_exists('PHPMailer') === false){
			require(sysConfig::getDirFsCatalog() . 'includes/classes/PhpMailer/class.phpmailer.php');
		}
		$Email = new PHPMailer();
		$Email->AddReplyTo($sendFromEmail,$sendFrom);
		$Email->SetFrom($sendFromEmail, $sendFrom);
		$Email->AddAddress($sendTo['email'], $sendTo['name']);

		$Email->Subject    = $this->parseTemplateSubject();
		$Email->AltBody    = "To view the message, please use an HTML compatible email viewer!";
		$Email->MsgHTML($this->parseTemplateBody());
		if (isset($sendTo['attach'])){
			$Email->AddAttachment($sendTo['attach']);
		}
		$Email->send();
	}
}
