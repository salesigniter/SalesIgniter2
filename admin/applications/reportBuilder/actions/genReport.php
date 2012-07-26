<?php
function parseModel(&$Query, $Model, $BaseTable)
{
	foreach($Model as $type => $tInfo){
		foreach($tInfo as $v){
			if ($type == 'relation'){
				$Query->leftJoin($BaseTable . '.' . $v . ' ' . $v);
				//echo 'LEFTJOIN::' . $BaseTable . '.' . $v . "\n";
				if (isset($_POST['model'][$v])){
					parseModel($Query, $_POST['model'][$v], $v);
				}
			}
			else {
				if (isset($_POST['sum']) && isset($_POST['sum'][$v])){
					$Query->addSelect('SUM(' . $_POST['sum'][$v] . ') as ' . $v);
				}else{
					$Query->addSelect($BaseTable . '.' . $v);
				}
				//echo 'SELECT::' . $BaseTable . '.' . $v . "\n";
			}
		}
	}
}

function parseArray($column){
	$return .= '<ul>';
	foreach($column as $k => $v){
		$return .= '<li>' . $k;
		if (is_array($v)){
			$return .= parseArray($v);
		}
		else{
			$return .= $v;
		}
		$return .= '</li>';
	}
	$return .= '</ul>';
	return $return;
}

function iterateResultBlock($Block, $TableName, $colTag)
{
	$return = '';
	foreach($Block as $ColumnName => $ColumnValue){
		$return .= '<' . $colTag . '>';
		if (is_numeric($ColumnName)){
			$return .= iterateResultBlock($ColumnValue, $TableName, $colTag);
		}else{
			if ($ColumnValue instanceof SesDateTime || $ColumnValue instanceof DateTime){
				$return .= $ColumnValue->format(sysLanguage::getDateFormat('short'));
			}
			elseif (is_array($ColumnValue) && $colTag != 'th') {
				$return .= '<div style="overflow:auto;height:100px;">' . parseArray($ColumnValue) . '</div>';
			}
			else {
				if ($colTag == 'th'){
					$return .= $TableName . '.' . $ColumnName;
				}
				else{
					$return .= $ColumnValue;
				}
			}
		}
		$return .= '</' . $colTag . '>';
	}
	return $return;
}

function parseResultRelation($Result, $RelName, $colTag){
	$return = iterateResultBlock($Result->$RelName, $RelName, $colTag);

	foreach($_POST['model'] as $TableName => $Something){
		if ($TableName != $RelName){
			if ($Result->contains($TableName)){
				$return .= parseResultRelation($Result->$TableName, $TableName, $colTag);
			}
		}
	}
	return $return;
}

$BaseTable = $_POST['baseTable'];
$Model = $_POST['model'];

$Table = Doctrine_Query::create();
$Table->from($BaseTable);
parseModel($Table, $_POST['model'][$BaseTable], $BaseTable);
if (isset($_POST['group_by'])){
	$Table->groupBy(implode(', ', $_POST['group_by']));
}
echo '<div style="margin-bottom:20px;border:1px solid black">' . $Table->getSqlQuery() . '</div>';
$Results = $Table->execute();

$body = '';
foreach($Results as $Result){
	if (!isset($headerRow)){
		$headerRow = '<tr>';
		$headerRow .= iterateResultBlock($Result, $BaseTable, 'th');
		foreach($_POST['model'] as $TableName => $Something){
			if ($TableName != $BaseTable){
				if ($Result->contains($TableName)){
					$headerRow .= parseResultRelation($Result, $TableName, 'th');
				}
			}
		}
		$headerRow .= '<tr>';
	}

	$body .= '<tr>';
	$body .= iterateResultBlock($Result, $BaseTable, 'td');
	foreach($_POST['model'] as $TableName => $Something){
		if ($TableName != $BaseTable){
			if ($Result->contains($TableName)){
				$body .= parseResultRelation($Result, $TableName, 'td');
			}
		}
	}
	$body .= '</tr>';
}
?>
<table>
	<thead>
	<?php echo $headerRow;?>
	</thead>
	<tbody>
	<?php echo $body;?>
	</tbody>
</table>

<?php
EventManager::attachActionResponse('', 'html');
