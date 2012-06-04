<?php
if (isset($_POST['imagesSortable'])){
	$imagesSortable = array();
	parse_str($_POST['imagesSortable'], &$imagesSortable);

	$images = array();
	foreach($imagesSortable['image'] as $displayOrder => $imageNumber){
		$ImageItem = $_POST['image'][$imageNumber];
		$images[] = $ImageItem;
	}
	$WidgetProperties['images'] = $images;
}
?>