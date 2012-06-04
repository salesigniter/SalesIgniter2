<?php
/**
 * This file is for functions that are the same in the admin as they are in the catalog, and is included in both sides
 */

/**
 * @param $json
 * @param bool $assoc
 * @param int $depth
 * @param int $options
 * @return array|mixed
 */
function recursive_json_decode($json, $assoc = false, $depth = 512, $options = 0){
	$data = json_decode($json, $assoc);
	foreach($data as $k => $dInfo){
		if (is_array($dInfo)){
			foreach($dInfo as $k2 => $dInfo2){
				$data[$k][$k2] = recursive_json_decode($dInfo2, $assoc);
			}
		}
		elseif (substr($dInfo, 0, 1) == '{' && substr($dInfo, -1) == '}'){
			$data[$k] = recursive_json_decode($dInfo, $assoc);
		}
	}
	return $data;
}
