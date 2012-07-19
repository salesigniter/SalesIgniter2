<?php
require(sysConfig::getDirFsCatalog() . 'includes/classes/template.php');

$SaleModule = $Editor->getSaleModule();
$PrintTemplate = $SaleModule->getPrintTemplate($_GET['type']);

$Construct = htmlBase::newElement('div')
	->attr('id', 'bodyContainer');
$ExtTemplateManager = $appExtension->getExtension('templateManager');
$LayoutBuilder = $ExtTemplateManager->getLayoutBuilder();
$LayoutBuilder->addVar('Sale', $Editor);
$LayoutBuilder->setLayoutId($PrintTemplate->layout_id);
$LayoutBuilder->build($Construct);

ob_start();
?>
<style type="text/css">
	@page {
		margin  : 0;
		padding : 0;
	}

	body {
		font-family: Arial;
		font-size: 10pt;
	}

	table, tbody, thead, tr, th, td { font-size: 8pt; }

	div.container {
		float : none;
		clear : both;
	}

	div.column {
	}

	hr {
		page-break-after : always;
		border           : 0;
	}

	.page-number:before {
		content : counter(page);
	}

</style>
<?php
echo $Construct->draw();
$myPdf = ob_get_contents();
ob_end_clean();
/*$dompdf = new DOMPDF();
$dompdf->set_base_path(sysConfig::get('DIR_FS_DOCUMENT_ROOT'));
$dompdf->load_html(utf8_decode($myPdf));
$dompdf->render();
$dompdf->stream('saved_pdf.pdf', array("Attachment" => 0));
//echo $dompdf->output_html();
*/
//echo $myPdf;
require(sysConfig::getDirFsCatalog() . 'ext/mpdf/mpdf.php');

$mpdf = new mPDF();
$mpdf->WriteHTML($myPdf);
$mpdf->Output();

EventManager::attachActionResponse('', 'exit');
