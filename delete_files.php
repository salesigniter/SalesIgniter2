<?php
set_time_limit(0);

function delete_files(){
	//delete all files beside the ignored folders and files
	global $ftpConn;
	$copyDir = $_POST['catalogPath'];
	$ignore = array();
	$ignore[] = 'images';
	$ignore[] = 'backupFiles';
	$ignore[] = 'backupDB';
	$ignore[] = 'templates';
	$ignore[] = 'delete_files.php';
	$ignore[] = 'extract.php';
	$ignore[] = 'extensions/imageRot/images';
	$ignore[] = 'extensions/pdfPrinter/images';
	$ignore[] = 'extensions/templateManager/widgetTemplates';
	$ignore[] = 'includes/classes/sesLicense';


	$RootDir = new RecursiveDirectoryIterator($copyDir);
	$Files = new RecursiveIteratorIterator($RootDir, RecursiveIteratorIterator::CHILD_FIRST);

	// Process all files and folders and add them to the zip file
	foreach ($Files as $File){
		if ($File->getBasename() == '.' || $File->getBasename() == '..') continue;

		$fullPath = $File->getPathname();
		$process = true;
		$cleaned = substr($fullPath, strlen($copyDir));
		$ftpCleaned = $cleaned;
		if ($File->isDir()){
			$cleaned .= '/';
		}
		foreach($ignore as $path){
			if (substr($cleaned, 0, strlen($path)) == $path){
				$process = false;
				break;
			}
		}

		if ($process === true){
			ftp_chmod($ftpConn, octdec('0777'), $ftpCleaned);
			if ($File->isDir()){
				rmdir($fullPath);
				ftp_rmdir($ftpConn, $ftpCleaned);

			}elseif ($File->isFile() || $File->isLink()) {
				unlink($fullPath);
				ftp_delete($ftpConn, $ftpCleaned);
			}
			//exit;

		}
	}
	ftp_chmod($ftpConn, octdec('0755'), 'extensions');
	ftp_chmod($ftpConn, octdec('0755'), 'extensions/imageRot');
	ftp_chmod($ftpConn, octdec('0755'), 'extensions/pdfPrinter');
	ftp_chmod($ftpConn, octdec('0755'), 'extensions/templateManager');
	ftp_chmod($ftpConn, octdec('0755'), 'extensions/templateManager/widgetTemplates');
	ftp_chmod($ftpConn, octdec('0755'), 'includes');
	ftp_chmod($ftpConn, octdec('0755'), 'includes/classes');
}

$ftpConn = ftp_connect($_POST['SYSTEM_FTP_SERVER']);
if ($ftpConn === false){
	die('Error ftp_connect');
}
else {
	$ftpCmd = ftp_login($ftpConn,$_POST['SYSTEM_FTP_USERNAME'] , $_POST['SYSTEM_FTP_PASSWORD']);
	if (!$ftpCmd){
		die('Error ftp_login');
	}
}



$ftpCmd = ftp_chdir($ftpConn, $_POST['SYSTEM_FTP_PATH']);
if (!$ftpCmd){
	die('Error ftp_chdir public_html');
}

delete_files();
ftp_close($ftpConn);

?>