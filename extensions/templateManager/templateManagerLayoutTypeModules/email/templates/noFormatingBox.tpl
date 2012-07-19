<?php
if (isset($box_id)){
	echo '<span id="' . $box_id . '">';
}
echo $boxContent;
if (isset($box_id)){
	echo '</span>';
}
?>