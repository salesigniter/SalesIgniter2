<?php
$tableGrid = htmlBase::newElement('newGrid')
	->setMainDataKey('layout_id')
	->allowMultipleRowSelect(true)
	->usePagination(false);

$tableGrid->addHeaderRow(array(
	'columns' => array(
		array('text' => 'Layout Name'),
		array('text' => 'Display Type'),
		array('text' => 'Layout Type')
	)
));

$buttArr = array(
	htmlBase::newElement('button')->usePreset('back')->setText('Back To Templates')->addClass('backButton'),
	htmlBase::newElement('button')->usePreset('new')->addClass('newButton'),
	htmlBase::newElement('button')->usePreset('copy')->setText('Duplicate')->addClass('duplicateButton')->disable(),
	htmlBase::newElement('button')->usePreset('edit')->addClass('configureButton')->disable(),
	htmlBase::newElement('button')->usePreset('edit')->setText('Edit Layout Template')->addClass('editButton')->disable(),
	htmlBase::newElement('button')->usePreset('delete')->addClass('deleteButton')->disable()
);

$iTemplate = Doctrine_Query::create()
	->from('TemplateManagerTemplates')
	->where('template_id = ?', $_GET['template_id'])
	->fetchOne();

if($iTemplate->Configuration['NAME']->configuration_value == 'codeGeneration'){
	$buttArr[] = htmlBase::newElement('button')->setText('GenerateCode')->addClass('generateCode')->disable();

}


$tableGrid->addButtons($buttArr);

$QLayouts = Doctrine_Query::create()
->select('layout_id, layout_name, layout_type, page_type, layout_settings')
->from('TemplateManagerLayouts')
->where('template_id = ?', $iTemplate->template_id)
->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
if ($QLayouts){
	foreach($QLayouts as $lInfo){
		$LayoutSettings = $lInfo['layout_settings'];
		$displayType = $lInfo['layout_type'];
		if ($lInfo['page_type'] == 'print'){
			$displayType = $LayoutSettings['layoutOrientation'];
		}
		$tableGrid->addBodyRow(array(
			'rowAttr' => array(
				'data-layout_id' => $lInfo['layout_id']
			),
			'columns' => array(
				array('addCls' => 'layoutName', 'text' => ucfirst($lInfo['layout_name'])),
				array('addCls' => 'displayType', 'text' => ucfirst($displayType)),
				array('addCls' => 'layoutType', 'text' => ucfirst($lInfo['page_type']))
			)
		));
	}
}
?>
<script type="text/javascript">
	var tID = '<?php echo $_GET['$iTemplate->template_id'];?>';
</script>
<div class="ui-widget ui-widget-content ui-corner-all" style="margin-right:5px;margin-left:5px;">
	<div style="margin:5px;"><?php echo $tableGrid->draw();?></div>
</div>
