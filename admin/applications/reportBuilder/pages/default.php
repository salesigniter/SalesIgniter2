Select A Base Table For This Report<br>
<?php
Doctrine_Core::loadAllModels();
echo '<select name="baseTable">';
echo '<option value="">Please Select A Table</option>';
$Models = Doctrine_Core::getLoadedModels();
sort($Models);
foreach($Models as $ModelName){
	echo '<option value="' . $ModelName . '">' . $ModelName . '</option>';
}
echo '</select>';
?>
<div></div>
<div id="ReportBuilder" class="column" style="width:400px;vertical-align: top;"></div>
<div id="ReportBuilderOutput" class="column" style="width:1200px;height:500px;overflow:auto;vertical-align: top;"></div>
