<?php
/*
	SalesIgniter E-Commerce System v1

	I.T. Web Experts
	http://www.itwebexperts.com

	Copyright (c) 2010 I.T. Web Experts

	This script and it's source is not redistributable
*/
chdir('../../../../');
require('includes/application_top.php');
require('includes/classes/uploadManager.php');

$json = array(
	'success' => true,
	'messages' => array()
);

switch ($_GET['action']) {
	case 'moveItem':
		$ftpRes = new SystemFTP();
		$ftpRes->connect();
		if ($ftpRes->changeDirectory($_POST['currentDir'])) {
			$moveToDir = $_POST['moveToDir'];
			foreach ($_POST['item'] as $itemName) {
				if (!$ftpRes->moveItem($itemName, $moveToDir . '/' . $itemName)) {
					$json['success'] = false;
					$json['messages'][] = 'Unable To Move Item "' . $itemName . '" To "' . $moveToDir . '"';
				}
			}
		} else {
			$json['success'] = false;
			$json['messages'][] = 'Unable To Change Directory To: ' . $_POST['currentDir'];
		}
		break;
	case 'editItem':
		$ftpRes = new SystemFTP();
		$ftpRes->connect();
		if ($ftpRes->changeDirectory($_POST['currentDir'])) {
			$oldName = $_POST['item_old_name'];
			$newName = $_POST['item_name'];
			$permissions = $_POST['permissions'];

			$fileName = $oldName;
			if ($oldName != $newName) {
				if (!$ftpRes->renameFile($oldName, $newName)) {
					$json['success'] = false;
					$json['messages'][] = 'Unable To Rename Item "' . $oldName . '" To "' . $newName . '"';
				} else {
					$fileName = $newName;
				}
			}
			if (!$ftpRes->changePermissions($fileName, $permissions)) {
				$json['success'] = false;
				$json['messages'][] = 'Unable To Change Item Permissions';
			}
		} else {
			$json['success'] = false;
			$json['messages'][] = 'Unable To Change Directory To: ' . $_POST['currentDir'];
		}
		break;
	case 'deleteItems':
		$ftpRes = new SystemFTP();
		$ftpRes->connect();
		if ($ftpRes->changeDirectory($_POST['currentDir'])){
			foreach($_POST['dir'] as $dirName){
				if (!$ftpRes->deleteDir($dirName)){
					$json['success'] = false;
					$json['messages'][] = 'Unable To Delete Directory: ' . $_POST['currentDir'] . '/' . $dirName;
				}
			}
			foreach($_POST['item'] as $fileName){
				if ($ftpRes->deleteFile($fileName)){
					$json['success'] = false;
					$json['messages'][] = 'Unable To Delete File: ' . $_POST['currentDir'] . '/' . $fileName;
				}
			}
		}else{
			$json['success'] = false;
			$json['messages'][] = 'Unable To Change Directory To: ' . $_POST['currentDir'];
		}
		break;
	case 'createDirectory':
		$ftpRes = new SystemFTP();
		$ftpRes->connect();
		if ($ftpRes->changeDirectory($_POST['currentDir'])) {
			$json['success'] = $ftpRes->createDirectory($_POST['dirName']);
		} else {
			$json['success'] = false;
			$json['messages'][] = 'Unable To Change Directory To: ' . $_POST['currentDir'];
		}
		break;
	case 'upload':
		if (!empty($_FILES)){
			$uploadDir = $_POST['uploadPath'];
			$mgr = new UploadManager($uploadDir, '644');
			if (isset($_POST['allowedTypes'])) {
				$mgr->setExtensions($_POST['allowedTypes']);
			}

			foreach($_FILES as $inputName => $fInfo){
				$file = new UploadFile($inputName);
				$success = true;
				if ($mgr->processFile($file) === false) {
					$success = false;
					if (isset($json)) {
						$json['success'] = false;
					}
				}

				$exception = $mgr->getException();
				$json['messages'][] = $exception->getMessage();
			}
		}else{
			$json['success'] = false;
			$json['messages'][] = 'File Did Not Upload, Please Check The Following';
			$json['messages'][] = 'PHP Max Upload: ' . ini_get('upload_max_filesize');
			$json['messages'][] = 'PHP Max Post: ' . ini_get('post_max_size');
		}

		if ($json['success'] === true) {
			$response = '<div id="status">success</div>' .
				'<div id="message">Upload Completed Successfully</div>';
		} else {
			$response = '<div id="status">error</div>' .
				'<div id="message">' . implode($json['messages'], '<br>' . "\n") . '</div>';
		}
		echo $response;
		itwExit();
		break;
}

if (isset($json)) {
	echo json_encode($json);
	itwExit();
} else {
	tep_redirect(itw_app_link(tep_get_all_get_params(array('action'))));
}
?>