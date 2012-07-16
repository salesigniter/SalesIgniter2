<?php
$serialLength = 9;
$status = itw_get_status_name((int)$_POST['default_status']);
$genTotal = (int)$_POST['gen_total'];
for($i = 0; $i < $genTotal; $i++){
	$exists = true;
	while($exists === true){
		$NewSerial = rand(str_repeat(1, $serialLength), str_repeat(9, $serialLength));
		$Qcheck = Doctrine_Query::create()
			->select('count(*) as total')
			->from('ProductsInventoryItemsSerials')
			->where('serial_number = ?', $NewSerial)
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		if ($Qcheck[0]['total'] == 0){
			$exists = false;
		}
	}

	$newSerials[] = array(
		'serial_number' => $NewSerial,
		'status_name' => $status
	);
}

EventManager::attachActionResponse(array(
	'success' => true,
	'serials' => $newSerials
), 'json');
