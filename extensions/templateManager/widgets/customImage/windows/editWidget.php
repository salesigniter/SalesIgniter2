<?php
ob_start();
?>
<style>
	#imagesSortable {
		list-style-type : none;
		margin          : 0;
		padding         : 0;
		position        : relative;
	}

	#imagesSortable li.sortable {
		display        : inline-block;
		vertical-align : top;
		margin         : 3px 3px 3px 0;
		padding        : 1px;
		min-width      : 350px;
		font-size      : 1em;
		text-align     : left;
		position       : relative;
	}

	#imagesSortable li.sortable .imageDelete {
		position : absolute;
		top      : -5px;
		right    : -5px;
	}

	#imagesSortable li select {
		margin-left  : .5em;
		margin-right : .5em;
		width        : 100%;
	}

	#imagesSortable li .fileManagerInput {
		width : 100%;
	}
</style>
<script type="text/javascript">
	$(document).ready(function () {
		$('#imagesSortable').sortable({
			tolerance            : 'pointer',
			placeholder          : 'ui-state-highlight',
			forcePlaceholderSize : true
		});

		$('.linkSizes').on('click', function () {
			if ($(this).hasClass('ui-icon-link')){
				$(this).removeClass('ui-icon-link').addClass('ui-icon-link-break')
			}
			else {
				$(this).removeClass('ui-icon-link-break').addClass('ui-icon-link')
			}
		});

		function setupListBlock() {
			var self = $(this);
			var Image = $(this).find('.previewImage');
			var ImageRealWidth = Image.get(0).width;
			var ImageRealHeight = Image.get(0).height;

			$(this).on('keyup', '.ui-tabs-panel .imageWidth', function () {
				var keepRatio = $(this).parent().parent().find('.linkSizes').hasClass('ui-icon-link');
				if (keepRatio === true){
					var Ratio = ImageRealWidth / ImageRealHeight;
					var RatioValue = parseInt($(this).val()) / Ratio;
					$(this).parent().parent().find('.imageHeight').val(parseInt(RatioValue));
					Image.height(RatioValue);
				}
				Image.width($(this).val());
			});

			$(this).on('keyup', '.ui-tabs-panel .imageHeight', function () {
				var keepRatio = $(this).parent().parent().find('.linkSizes').hasClass('ui-icon-link');
				if (keepRatio === true){
					var Ratio = ImageRealHeight / ImageRealWidth;
					var RatioValue = parseInt($(this).val()) / Ratio;
					$(this).parent().parent().find('.imageWidth').val(parseInt(RatioValue));
					Image.width(RatioValue);
				}
				Image.height($(this).val());
			});

			$(this).on('onSelect', '.ui-tabs-panel .ui-filemanager-input', function (e, selected) {
				Image.attr('src', selected[0]);
				Image.width('auto');
				Image.height('auto');
				ImageRealWidth = Image.get(0).width;
				ImageRealHeight = Image.get(0).height;

				self.parentsUntil('.ui-tabs-panel').last().find('.imageWidth').val(ImageRealWidth);
				self.parentsUntil('.ui-tabs-panel').last().find('.imageHeight').val(ImageRealHeight);
			});
		}

		$('#imagesSortable li.sortable').each(function () {
			setupListBlock.apply(this);
		});

		$('#imagesTable').find('.addMainBlock').click(function () {
			var inputKey = 0;
			while($('#imagesSortable').find('li[data-input_key=' + inputKey + '].sortable').size() > 0){
				inputKey++;
			}

			var liHtml = '<div class="makeTabs"><ul>';

		<?php foreach(sysLanguage::getLanguages() as $lInfo){ ?>
			liHtml += '<li>' +
				'<a href="#image_' + inputKey + '_<?php echo $lInfo['id'];?>_tab"><?php echo $lInfo['showName']('<br>');?></a>' +
				'</li>';
			<?php } ?>
			liHtml += '</ul>';

		<?php foreach(sysLanguage::getLanguages() as $lInfo){ ?>
			liHtml += '<div id="image_' + inputKey + '_<?php echo $lInfo['id'];?>_tab"><table cellpadding="2" cellspacing="0" border="0" width="100%">' +
				'<tr>' +
				'	<td valign="top"><img class="previewImage"></td>' +
				'</tr>' +
				'<tr>' +
				'	<td valign="top"><input type="text" class="fileManagerInput" data-is_multiple="false" data-files_source="<?php echo sysConfig::getDirFsCatalog();?>templates/" name="image[' + inputKey + '][<?php echo $lInfo['id'];?>][source]" value=""></td>' +
				'</tr>' +
				'<tr>' +
				'	<td valign="top" class="imageResizerContainer"></td>' +
				'</tr>' +
				'<tr>' +
				'	<td valign="top" class="systemLinkMenuContainer"></td>' +
				'</tr>' +
				'</table></div>';
			<?php } ?>

			liHtml += '<span class="ui-icon ui-icon-circle-close imageDelete" tooltip="Delete Image"></span></div>';

			var $newLi = $('<li></li>')
				.addClass('sortable')
				.attr('id', 'image_' + inputKey)
				.attr('data-input_key', inputKey)
				.html(liHtml);

		<?php foreach(sysLanguage::getLanguages() as $lInfo){ ?>
			$newLi.find('#image_' + inputKey + '_<?php echo $lInfo['id'];?>_tab').each(function () {
				$.newSystemLinkMenu($(this), 'image[' + inputKey + '][<?php echo $lInfo['id'];?>]');
				$.newImageResizer($(this), 'image[' + inputKey + '][<?php echo $lInfo['id'];?>]');
			});
			<?php } ?>

			$newLi.find('.makeTabs').tabs();
			setupListBlock.apply($newLi.get(0));

			$('#imagesSortable').append($newLi);
			$('#imagesSortable').sortable('refresh');
		});

		$('.imageDelete').live('click', function () {
			$(this).parentsUntil('ol').last().remove();
		});

		$('.saveButton').click(function () {
			$('input[name=imagesSortable]').val($('#imagesSortable').sortable('serialize'));
		});
	})
	;
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
		array('text' => '<span class="ui-icon ui-icon-plusthick addMainBlock"></span>')
	)
));

function parseImages($images, &$i)
{
	$ItemTemplates = array();
	foreach($images as $iInfo){
		$Tabs = htmlBase::newElement('tabs');
		foreach(sysLanguage::getLanguages() as $lInfo){
			$baseInputName = 'image[' . $i . '][' . $lInfo['id'] . ']';
			$data = (isset($iInfo->{$lInfo['id']}) ? $iInfo->{$lInfo['id']} : false);

			$textInput = htmlBase::newElement('fileManager')
				->setName($baseInputName . '[source]')
				->val((isset($data->source) ? $data->source : ''));

			$imagePreview = htmlBase::newElement('image')
				->addClass('previewImage')
				->setSource((isset($data->source) ? $data->source : ''));

			$imageResizer = htmlBase::newElement('imageResizer', array(
				'data' => $data
			))->setName($baseInputName);

			$systemLinkMenu = htmlBase::newElement('systemLinkMenu', array(
				'data' => (isset($data->link) ? $data->link : false)
			))->setName($baseInputName . '[link]');

			$ImageTable = htmlBase::newElement('table')
				->setCellPadding(3)
				->setCellSpacing(0)
				->css('width', '100%');

			$ImageTable->addBodyRow(array(
				'columns' => array(
					array('text' => $imagePreview->draw())
				)
			));

			$ImageTable->addBodyRow(array(
				'columns' => array(
					array('text' => $textInput->draw())
				)
			));

			$ImageTable->addBodyRow(array(
				'columns' => array(
					array(
						'align' => 'center',
						'text'  => $imageResizer->draw()
					)
				)
			));

			$ImageTable->addBodyRow(array(
				'columns' => array(
					array('text' => $systemLinkMenu->draw())
				)
			));

			$Tabs
				->addTabHeader('image_' . $i . '_' . $lInfo['id'] . '_tab', array('text' => $lInfo['showName']('<br>')))
				->addTabPage('image_' . $i . '_' . $lInfo['id'] . '_tab', array('text' => $ImageTable->draw()));
		}
		$ItemTemplates[] = htmlBase::newElement('li')
			->addClass('sortable')
			->attr('id', 'image_' . $i)
			->attr('data-input_key', $i)
			->html($Tabs->draw() . '<span class="ui-icon ui-icon-circle-close imageDelete" tooltip="Delete Image"></span>')
			->draw();
		$i++;
	}

	return implode('', $ItemTemplates);
}

function parseImage($item, &$i)
{
	global $AppArray, $CatArr, $LinkTypes, $LinkTargets, $template;
}

$Images = '';
if (isset($WidgetSettings->images)){
	$i = 0;
	$Images = parseImages($WidgetSettings->images, &$i);
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
