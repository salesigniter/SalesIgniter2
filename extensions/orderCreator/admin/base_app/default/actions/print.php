<?php
require(sysConfig::getDirFsCatalog() . 'includes/classes/template.php');

$SaleModule = $Editor->getSaleModule();
$PrintTemplate = $SaleModule->getPrintTemplate($_GET['type']);

$Construct = htmlBase::newElement('p')->attr('id', 'bodyContainer');
$ExtTemplateManager = $appExtension->getExtension('templateManager');
$LayoutBuilder = $ExtTemplateManager->getLayoutBuilder();
$LayoutBuilder->addVar('Sale', $Editor);
$LayoutBuilder->setLayoutId($PrintTemplate->layout_id);
$LayoutBuilder->build($Construct);

ob_start();
?>
<style type="text/css">
	@page {
		margin: 0;
	}
	div.column { display:block; }

	<?php
	//echo $addCss;
	?>
		/*body {
		 margin-top: 3.5cm;
		 margin-bottom: 3cm;
		 margin-left: 1.5cm;
		 margin-right: 1.5cm;
		 font-family: sans-serif;
		 text-align: justify;
	 }*/

	.container{
		display: block;
	}
	.column{
		display: inline-block;
		vertical-align: top;
	}

	hr {
		page-break-after: always;
		border: 0;
	}

	.page-number:before {
		content: counter(page);
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

require(sysConfig::getDirFsCatalog() . 'ext/mpdf/mpdf.php');

$mpdf=new mPDF();
$mpdf->WriteHTML($myPdf);
$mpdf->Output();

EventManager::attachActionResponse('', 'exit');
