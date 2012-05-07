<?php
$CategoriesToGroups =& $Category->FeaturedManagerCategoriesToGroups;
if ($CategoriesToGroups && $CategoriesToGroups->count() > 0){
	$CategoriesToGroups->delete();
}

if (isset($_POST['groups'])){
	foreach($_POST['groups'] as $groupId){
		$CategoriesToGroups[]->featured_group_id = $groupId;
	}
}
$Category->save();
