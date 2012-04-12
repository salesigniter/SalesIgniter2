<?php
	require(sysConfig::getDirFsCatalog() . 'includes/javascript/classes.js');
?>
function getUrlVars() {
	var vars = [], hash;
	var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');

	for(var i = 0; i < hashes.length; i++){
		if (hashes[i] == 'showErrors'){
			hashes[i] = 'showErrors=true';
		}
		if (hashes[i] == 'noCache'){
			hashes[i] = 'noCache=true';
		}
		hash = hashes[i].split('=');
		vars.push(hash[0]);
		vars[hash[0]] = hash[1];
	}
	return vars;
}

/* Declare Global Variables For All Javascript Access -- BEGIN -- */
var $_GET = getUrlVars();
/* Declare Global Variables For All Javascript Access -- END -- */

var CKEDITOR_BASEPATH = '<?php echo sysConfig::getDirWsAdmin() . 'rental_wysiwyg/';?>';
var allGetParams = '<?php echo substr(tep_get_all_get_params(), 0, -1);?>';
var SID = '<?php echo SID;?>';
var sessionName = '<?php echo Session::getSessionName();?>';
var sessionId = '<?php echo Session::getSessionId();?>';
var request_type = '<?php echo sysConfig::get('REQUEST_TYPE');?>';
var thisFile = '<?php echo basename($_SERVER['PHP_SELF']);?>';
var thisApp = '<?php echo $App->getAppName();?>';
var thisAppPage = '<?php echo $App->getAppPage();?>';
var thisAppExt = '<?php echo (isset($_GET['appExt']) && !empty($_GET['appExt']) ? $_GET['appExt'] : null);?>';
var productID = '<?php echo (int)(isset($_GET['pID']) ? $_GET['pID'] : '0');?>';

jsCurrencies.setCode('<?php echo $CurrencyInfo['code'];?>');
jsCurrencies.setTitle('<?php echo $CurrencyInfo['title'];?>');
jsCurrencies.setSymbolLeft('<?php echo $CurrencyInfo['symbol_left'];?>');
jsCurrencies.setSymbolRight('<?php echo $CurrencyInfo['symbol_right'];?>');
jsCurrencies.setDecimalPoint('<?php echo $CurrencyInfo['decimal_point'];?>');
jsCurrencies.setThousandsPoint('<?php echo $CurrencyInfo['thousands_point'];?>');
jsCurrencies.setDecimalPlaces(<?php echo $CurrencyInfo['decimal_places'];?>);
jsCurrencies.setValue(<?php echo $CurrencyInfo['value'];?>);

jsLanguage.setDateFormat({
	short: '<?php echo sysLanguage::getJsDateFormat('short');?>',
	long: '<?php echo sysLanguage::getJsDateFormat('long');?>'
});
<?php
if (sysLanguage::hasJavascriptDefines() === true){
	foreach(sysLanguage::getJavascriptDefines() as $k => $v){
		echo '			jsLanguage.set(\'' . $k . '\', "' . $v . '");' . "\n";
	}
}

foreach(sysConfig::getJavascriptConfigs() as $k => $v){
	echo '			jsConfig.set(\'' . $k . '\', "' . $v . '");' . "\n";
}
?>
