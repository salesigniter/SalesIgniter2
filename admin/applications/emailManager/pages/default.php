<?php
$Qtemplates = Doctrine_Query::create()
	->from('EmailTemplates')
	->where('email_module = ?', $_GET['module']);

$TableGrid = htmlBase::newGrid()
	->setMainDataKey('template_id')
	->allowMultipleRowSelect(true)
	->usePagination(true)
	->useSorting(true)
	->setQuery($Qtemplates);

$TableGrid->addButtons(array(
	htmlBase::newButton()->addClass('testEmailButton')->usePreset('email')->setText('Send Test Email')->disable(),
	htmlBase::newButton()->addClass('newButton')->usePreset('new'),
	htmlBase::newButton()->addClass('editButton')->usePreset('edit')->disable(),
	htmlBase::newButton()->addClass('deleteButton')->usePreset('delete')->disable()
));

$TableGrid->addHeaderRow(array(
	'columns' => array(
		array('text' => 'Template Name'),
		array('text' => 'Template Status'),
		array('text' => 'Sent On Event')
	)
));

$Results = $TableGrid->getResults(false);
if ($Results->count() > 0){
	foreach($Results as $Template){
		$TableGrid->addBodyRow(array(
			'rowAttr' => array(
				'data-template_id' => $Template->template_id
			),
			'columns' => array(
				array('text' => $Template->template_name),
				array('align' => 'center', 'text' => htmlBase::newIcon()->setType(($Template->template_status == 1 ? 'enabled' : 'disabled'))),
				array('text' => $Template->email_module_event_key)
			)
		));
	}
}
?>
<div class="ui-widget ui-widget-content ui-corner-all" style="margin:5px;">
	<div style="margin:5px;"><?php echo $TableGrid->draw();?></div>
</div>
