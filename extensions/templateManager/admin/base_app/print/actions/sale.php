<?php
require(sysConfig::getDirFsCatalog() . 'includes/classes/Order/Base.php');

$SaleType = $_GET['sale_type'];
$SaleId = $_GET['sale_id'];
$PrintType = (isset($_GET['print_type']) ? $_GET['print_type'] : 'default');
$Revision = (isset($_GET['revision']) ? $_GET['revision'] : 0);

$Sale = AccountsReceivable::getSale($SaleType, $SaleId, $Revision);
$SaleModule = $Sale->getSaleModule();
$PrintTemplate = $SaleModule->getPrintTemplate($PrintType);

$Construct = htmlBase::newElement('p')->attr('id', 'bodyContainer');
$LayoutBuilder = $TemplateManager->getLayoutBuilder();
$LayoutBuilder->setLayoutId($PrintTemplate->layout_id);
$LayoutBuilder->addVar('Sale', $Sale);
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

$mpdf=new mPDF();
$mpdf->WriteHTML($myPdf);
$mpdf->Output();

EventManager::attachActionResponse('', 'exit');
