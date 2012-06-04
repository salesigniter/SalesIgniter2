<?php
chdir('../../../../admin/');

require('includes/application_top.php');

$stylesheetLink = sysConfig::getDirWsCatalog() . 'extensions/templateManager/catalog/globalFiles/stylesheet.php?' .
	'&env=admin' .
	'&' . Session::getSessionName() . '=' . Session::getSessionId() .
	'&tplDir=' . sysConfig::get('TEMPLATE_DIRECTORY') .
	'&import=' . implode(',', $App->getStylesheetFiles()) .
	'&noCache';

$javascriptLink = sysConfig::getDirWsCatalog() . 'extensions/templateManager/catalog/globalFiles/javascript.php?' .
	'&env=admin' .
	'&' . Session::getSessionName() . '=' . Session::getSessionId() .
	'&tplDir=' . sysConfig::get('TEMPLATE_DIRECTORY') .
	'&import=' . implode(',', $App->getJavascriptFiles()) .
	'&noCache';
?>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="<?php echo $stylesheetLink;?>" />
		<script type="text/javascript">
			var allGetParams = '<?php echo substr(tep_get_all_get_params(), 0, -1);?>';
			var SID = '<?php echo SID;?>';
			var sessionName = '<?php echo Session::getSessionName();?>';
			var sessionId = '<?php echo Session::getSessionId();?>';
			var request_type = '<?php echo sysConfig::get('REQUEST_TYPE');?>';
			var thisFile = '<?php echo basename($_SERVER['PHP_SELF']);?>';
			var thisApp = '<?php echo $App->getAppName();?>';
			var thisAppPage = '<?php echo $App->getAppPage();?>';
		</script>
		<script><?php require('includes/javascript/jsAppTop.php');?></script>
		<script type="text/javascript" src="<?php echo $javascriptLink;?>"></script>
		<script>
			$(document).ready(function () {
				$('.makeFileManager').filemanager({
					onSelect: function(e, selected){
						window.opener.CKEDITOR.tools.callFunction($_GET['CKEditorFuncNum'], selected);
					}
				});
			});
		</script>
	</head>
	<body>
		<div class="makeFileManager" data-files_source="<?php echo (isset($_GET['filesSource']) ? $_GET['filesSource'] : sysConfig::getDirFsCatalog() . 'templates/');?>"></div>
	</body>
</html>
<?php
require('includes/application_bottom.php');
?>