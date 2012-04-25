<?php
ob_start();
?>
<style>
	#imagesSortable { list-style-type: none; margin: 0; padding: 0; }
	#imagesSortable li { display:inline-block;vertical-align: top;margin: 3px 3px 3px 0; padding: 1px; width: 350px; font-size: 1em; text-align: left; }
	#imagesSortable li div { cursor: move;margin: 0;padding: 3px;border: 1px solid black;background:#ffffff; }
	#imagesSortable li .ui-icon-closethick {
		float  : right;
		margin : .5em;
	}

	#imagesSortable li select {
		margin-left  : .5em;
		margin-right : .5em;
	}
</style>
<script src="<?php echo sysConfig::getDirWsCatalog();?>ext/jQuery/ui/jquery.ui.selectmenu.js"></script>
<link rel="stylesheet" href="<?php echo sysConfig::getDirWsCatalog();?>ext/jQuery/themes/smoothness/jquery.ui.selectmenu.css" type="text/css" media="screen,projection" />
<script type="text/javascript">
$(document).ready(function () {
	$('#imagesSortable').sortable({
		tolerance: 'pointer',
		placeholder: 'ui-state-highlight',
		forcePlaceholderSize: true
	});

	$('#imagesTable').find('.addMainBlock').click(function () {
		var inputKey = 0;
		while($('#imagesSortable > li[data-input_key=' + inputKey + ']').size() > 0){
			inputKey++;
		}

		var $newLi = $('<li></li>').attr('id', 'image_' + inputKey);
		$newLi.html('<div><table cellpadding="2" cellspacing="0" border="0" width="100%">' +
			'<tr>' +
			'<td valign="top"><table cellpadding="2" cellspacing="0" border="0">' +
		<?php foreach(sysLanguage::getLanguages() as $lInfo){ ?>
			'<tr>' +
				'<td><?php echo $lInfo['showName']('&nbsp;');?></td>' +
				'<td><input type="text" class="fileManager" data-is_multiple="true" data-files_source="<?php echo sysConfig::getDirFsCatalog();?>templates/" name="image_source[' + inputKey + '][<?php echo $lInfo['id'];?>]" value=""></td>' +
				'</tr>' +
		<?php } ?>
			'</table></td>' +
			'<td valign="top">' +
			'<span class="ui-icon ui-icon-closethick imageDelete" tooltip="Delete Image"></span>' +
			'</td>' +
			'</tr>' +
			'<tr>' +
			'<td valign="top" colspan="2" class="systemLinkMenuContainer"></td>' +
			'</tr>' +
			'</table></div>');
		
		$(document).trigger('newSystemLinkMenu', [$newLi, 'image_link']);

		$('#imagesSortable').append($newLi);
		$('#imagesSortable').sortable('refresh');
	});

	$('.imageDelete').live('click', function () {
		$(this).parentsUntil('ol').last().remove();
	});

	$('.saveButton').click(function () {
		$('input[name=imagesSortable]').val($('#imagesSortable').sortable('serialize'));
	});
});
</script>
<?php
$editTable = htmlBase::newElement('table')
	->setId('imagesTable')
	->setCellPadding(2)
	->setCellSpacing(0);

$editTable->addBodyRow(array(
	'columns' => array(
		array('text' => '<b>Images</b>')
	)
));

	$editTable->addBodyRow(array(
		'columns' => array(
			array('text' => '<span class="ui-icon ui-icon-plusthick addMainBlock"></span><span class="ui-icon ui-icon-closethick"></span>')
		)
	));

	function parseImage($item, &$i) {
		global $AppArray, $CatArr, $LinkTypes, $LinkTargets, $template;

		$data = $item->link;

		$textInputs = '<table cellpadding="2" cellspacing="0" border="0">';
		foreach(sysLanguage::getLanguages() as $lInfo){
			$textInput = htmlBase::newElement('input')
				->addClass('fileManager')
				->setName('image_source[' . $i . '][' . $lInfo['id'] . ']')
				->val((isset($data->image->{$lInfo['id']}) ? $data->image->{$lInfo['id']} : ''));
			$textInputs .= '<tr>' .
				'<td>' . $lInfo['showName']('&nbsp;') . '</td>' .
				'<td>' . $textInput->draw() . '</td>' .
				'</tr>';
		}
		$textInputs .= '</table>';

		$systemLinkMenu = htmlBase::newElement('systemLinkMenu', array(
			'data' => (isset($data) ? $data : false)
		))->setName($baseInputName);

		$itemTemplate = '<li id="image_' . $i . '" data-input_key="' . $i . '">' .
			'<div><table cellpadding="2" cellspacing="0" border="0" width="100%">' .
			'<tr>' .
			'<td valign="top">' . $textInputs . '</td>' .
			'<td valign="top"><span class="ui-icon ui-icon-closethick imageDelete" tooltip="Delete Image"></span></td>' .
			'</tr>' .
			'<tr>' .
			'<td valign="top" colspan="2">' . $systemLinkMenu->draw() . '</td>' .
			'</tr>' .
			'</table>' .
			'</div>' .
			'</li>';
		$i++;

		return $itemTemplate;
	}

$Images = '';
	if (isset($WidgetSettings->images)){
		$i = 0;
		foreach($WidgetSettings->images as $iInfo){
			$Images .= parseImage($iInfo, &$i);
		}
	}

	$editTable->addBodyRow(array(
		'columns' => array(
			array('text' => '<ol id="imagesSortable" class="ui-widget sortable">' . $Images . '</ol>')
		)
	));

echo $editTable->draw();
echo '<input type="hidden" name="imagesSortable" value="">';
$fileContent = ob_get_contents();
ob_end_clean();

$WidgetSettingsTable->addBodyRow(array(
	'columns' => array(
		array(
			'colspan' => 2,
			'text'    => $fileContent
		)
	)
));
