<?php
$ProductImage = htmlBase::newElement('fileManager')
	->css(array(
	'width'      => '100%',
	'box-sizing' => 'border-box'
))
	->setName('products_image')
	->val($Product->getImage());

$productDesigner = $appExtension->getExtension('productDesigner');
if ($productDesigner !== false && $productDesigner->isEnabled() === true){
	$ProductImageBack = htmlBase::newElement('fileManager')
		->setName('products_image_back');
}

if (isset($Product)){
	$zoomIcon = htmlBase::newElement('icon')->setType('zoomIn');
	$deleteIcon = htmlBase::newElement('icon')->setType('closeThick')->addClass('deleteImage');
	$imgSrc = 'images/';
	$thumbSrc = 'imagick_thumb.php?width=80&height=80&imgSrc=' . 'images/';
}
?>
<table cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td class="main" valign="top"><?php echo sysLanguage::get('TEXT_PRODUCTS_IMAGE'); ?></td>
	</tr>
	<tr>
		<td class="main" valign="top"><?php echo $ProductImage->draw();?></td>
	</tr>
	<?php
	if (isset($ProductImageBack)){
		?>
		<tr>
			<td class="main" valign="top"><?php echo 'Products Image Back:'; ?></td>
		</tr>
		<tr>
			<td class="main" valign="top"><?php echo $ProductImageBack->draw(); ?></td>
		</tr>
		<?php
	}
	?>
	<tr>
		<td>
			<hr />
		</td>
	</tr>
	<tr>
		<td>
			<table cellpadding="3" cellspacing="0" border="0">
				<thead>
				<tr>
					<th><b>Additional Images</b></th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td>Add Image(s)</td>
				</tr>
				<tr>
					<td><?php
						echo htmlBase::newFileManager()
							->allowMultiple('true')
							->setName('new_additional_image')
							->css(array(
							'width'          => '50%',
							'box-sizing'     => 'border-box',
							'margin'         => '0 5px',
							'vertical-align' => 'middle'
						))->draw()
						?><span class="ui-icon ui-icon-plusthick addAdditionalImage" style="vertical-align: middle;"></span></td>
				</tr>
				<tr>
					<td class="additionalImagesList"><?php
						$images = array();
						foreach($Product->getAdditionalImages() as $imgInfo){
							echo '<div class="ui-widget ui-widget-content additionalImageBox" style="width:300px;display:inline-block;margin:10px;text-align:center;padding:5px;position: relative;vertical-align: top;height: 190px;">' .
								'<span class="ui-icon ui-icon-closethick removeAdditionalImage" style="position: absolute;top: -12px;right: -12px;"></span>' .
								'<div style="height:160px;"><img src="imagick_thumb.php?width=150&height=150&path=rel&imgSrc=' . $imgInfo['file_name'] . '"></div>' .
								htmlBase::newFileManager()
									->allowMultiple('false')
									->setName('additional_image[]')
									->val($imgInfo['file_name'])
									->css(array(
									'width'      => '95%',
									'box-sizing' => 'border-box',
									'margin'     => '0 5px'
								))->draw() .
								'</div>';
						}
						?>
						<div class="ui-helper-clearfix"></div>
					</td>
				</tr>
				</tbody>
			</table>
		</td>
	</tr>
</table>