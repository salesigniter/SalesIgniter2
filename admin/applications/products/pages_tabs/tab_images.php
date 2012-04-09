<?php
$ProductImage = htmlBase::newElement('fileManager')
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
		<td class="main" valign="top"><?php echo $ProductImage->draw();?></td>
	</tr>
	<?php
	if (isset($ProductImageBack)){
		?>
		<tr>
			<td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
		</tr>
		<tr>
			<td class="main" valign="top"><?php echo 'Products Image Back:'; ?></td>
			<td class="main" valign="top"><?php echo $ProductImageBack->draw(); ?></td>
		</tr>
		<?php
	}
	?>
	<tr>
		<td colspan="2">
			<hr />
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<table cellpadding="3" cellspacing="0" border="0">
				<thead>
				<tr>
					<th><b>Additional Images</b></th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td colspan="2"><?php
						$images = array();
						foreach($Product->getAdditionalImages() as $imgInfo){
							$images[] = $imgInfo['file_name'];
						}
						echo htmlBase::newElement('fileManager')
							->setName('additional_images')
							->val(implode(',', $images))
							->draw();
						?></td>
				</tr>
				</tbody>
			</table>
		</td>
	</tr>
</table>