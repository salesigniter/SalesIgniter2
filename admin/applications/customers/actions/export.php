<?php
$toExport = explode(',', $_GET['customer_id']);
$exportColumns = explode(',', $_GET['export_columns']);

$ExportModule->setFormat('csv');
$ExportModule->setAction('export');
$ExportModule->setExportIds($toExport);
$ExportModule->setExportColumns($exportColumns);
$ExportModule->beforeActionProcess();
$ExportModule->perform();
$ExportModule->afterActionProcess();

tep_redirect(itw_app_link(null, 'customers', 'default'));
?>