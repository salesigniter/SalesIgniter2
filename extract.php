<?php
set_time_limit(0);

/*$_POST['DIR_WS_CATALOG'] = '/';
$_POST['SYSTEM_UPGRADE_USERNAME'] = 'rentasun';
$_POST['SYSTEM_UPGRADE_PASSWORD'] = '4b814d2d15c6b8003cac9af653460238';
$_POST['HTTP_DOMAIN_NAME'] = 'rentasurfboard.net';
$_POST['SYSTEM_VERSION'] = '1';
$_POST['DIR_FS_DOCUMENT_ROOT'] = '/home/rentasun/public_html/';
$_POST['SYSTEM_FTP_SERVER'] = 'rentasurfboard.net';
$_POST['SYSTEM_FTP_USERNAME'] = 'rentasun';
$_POST['SYSTEM_FTP_PASSWORD'] = 'B74UqLBh~5XI';*/

function extract_archive(){
	global $ftpConn;

	$postVars = array(
		'action=process',
		'version=' . $_POST['SYSTEM_VERSION'],
		'username=' . $_POST['SYSTEM_UPGRADE_USERNAME'],
		'password=' . $_POST['SYSTEM_UPGRADE_PASSWORD'],
		'domain=' . $_POST['HTTP_DOMAIN_NAME'],
		'allAlowedExtensions=1'
	);

	    unlink($_POST['DIR_FS_DOCUMENT_ROOT']. $_POST['DIR_WS_CATALOG']. 'backupFiles/cart.zip');
	    $File = fopen($_POST['DIR_FS_DOCUMENT_ROOT'].$_POST['DIR_WS_CATALOG']. 'backupFiles/cart.zip', 'w+');
		//$ErrFile = fopen('transfer_log', 'w+');
	    //ftruncate($File, -1);
	    $last = 0;
	    $connectWritten = false;
	    function report($download_size, $downloaded, $upload_size, $uploaded) {
		    global $connectWritten;
		    if ($downloaded > 0 && $download_size > 0){
			    $percent = ($downloaded / $download_size);
		    }
		    else {
			    $percent = 0;
		    }

			$downloadedDone = ($downloaded > 0 ? number_format(($downloaded / 1024) / 1024, 2) : 0);
			$downloadedLeft = ($download_size > 0 ? number_format(($download_size / 1024) / 1024, 2) : 0);
			if ($downloadedLeft == 0){
				if ($connectWritten === false){
					$connectWritten = true;
				}
			}
			else {
				$realPercent = (($downloadedDone / $downloadedLeft) * 16) + 4;
				if ($realPercent % 2){
				}
			}
			return 0;
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://www.itwebexperts.com/sesUpgrades/installer/' . $_POST['SYSTEM_VERSION'] . '/actions/getCode.php');
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BUFFERSIZE, 256);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $postVars));

		curl_setopt($ch, CURLOPT_FILE, $File);
		curl_setopt($ch, CURLOPT_NOPROGRESS, false);
		curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, 'report');

	    $result = curl_exec($ch);
	    curl_close($ch);
	    fclose($File);

		$NewCode = new ZipArchive();
		$NewCode->open($_POST['DIR_FS_DOCUMENT_ROOT']. $_POST['DIR_WS_CATALOG'].'backupFiles/cart.zip');
		$numOfFiles = $NewCode->numFiles;

		$extractPath = $_POST['DIR_FS_DOCUMENT_ROOT']. $_POST['DIR_WS_CATALOG'];
		if (substr($extractPath, 0, -1) == '/' && substr('/', 0, 1) == '/'){
			$extractPath = substr($extractPath, 0, -1);
		}
		$extractPath .= '/';
		$extractPath = str_replace('//', '/', $extractPath);

		for($i = 0; $i < $numOfFiles; $i++){
			$realPercent = (($i / $numOfFiles) * 30) + 20;

			$zipPathAbs = $NewCode->getNameIndex($i);
			if ($zipPathAbs == '/') {
				continue;
			}

			$zipPathRel = $zipPathAbs;
			if (substr($zipPathAbs, 0, 1) == '/'){
				$zipPathRel = substr($zipPathRel, 1, strlen($zipPathRel));
			}

			if (substr($zipPathAbs, -1) == '/'){
				if (!is_dir($extractPath . $zipPathRel)){
					$pathArr = explode('/', $zipPathRel);
					$lastDir = '';
					foreach($pathArr as $p){
						if (empty($p)) {
							continue;
						}

						if (!is_dir($extractPath . $lastDir . $p)){
							$ftpCmd = ftp_mkdir($ftpConn, $lastDir . $p);
							//error_log('ftp_mkdir[' . ($ftpCmd ? 'true' : 'false') . '][' . __LINE__ . ']: ' . $lastDir . $p . "\n", 3, realpath('../transfer_log'));
							if (!$ftpCmd){
								die('Error ftp_mkdir: ' . $lastDir . $p);
							}
						}
						$lastDir .= $p . '/';
					}
				}
			}
			else {
				$file = $NewCode->getStream($zipPathAbs);

				$pathChk = explode('/', $zipPathRel);
				$lastDir = '';
				for($j=0, $n=sizeof($pathChk); $j>$n; $j++){
					if (($j + 1) == $n) break;

					if (empty($pathChk[$j])) {
						continue;
					}

					if (!is_dir($extractPath . $lastDir . $pathChk[$j])){
						$ftpCmd = ftp_mkdir($ftpConn, $lastDir . $pathChk[$j]);
						//error_log('ftp_mkdir[' . ($ftpCmd ? 'true' : 'false') . '][' . __LINE__ . ']: ' . $lastDir . $pathChk[$j] . "\n", 3, realpath('../transfer_log'));
						if (!$ftpCmd){
							die('Error ftp_mkdir: ' . $lastDir . $pathChk[$j]);
						}
					}
					$lastDir .= $pathChk[$j] . '/';
				}

				$ftpCmd = ftp_fput($ftpConn, $zipPathRel, $file, FTP_BINARY);
				//error_log('ftp_fput[' . ($ftpCmd ? 'true' : 'false') . '][' . __LINE__ . ']: ' . $zipPathRel . '( ' . $zipPathAbs . ' )' . "\n", 3, realpath('../transfer_log'));
				if (!$ftpCmd){
					die('Error ftp_fput ' . $zipPathRel . '( ' . $zipPathAbs . ' )');
				}

				if (substr(basename($zipPathAbs), 0, 12) == '.installDate'){
					$installDate = substr(basename($zipPathAbs), 13);
				}
			}
		}
		$NewCode->close();

		//ftp_put($ftpConn, 'includes/classes/sesLicense', $extractPath . 'install/sesLicense', FTP_BINARY);

		$ftpCmd = ftp_chmod($ftpConn, octdec('0777'), 'includes/configure.xml');
		if (!$ftpCmd){
			die('Error chmod 777 ' . $extractPath . 'includes/configure.xml');
		}
		$configXml = simplexml_load_file($extractPath . 'includes/configure.xml');
		$configXml->config[0]->value = $_POST['HTTP_DOMAIN_NAME'];
		$configXml->config[1]->value = $_POST['HTTPS_DOMAIN_NAME'];
		$configXml->config[2]->value = $_POST['DIR_WS_CATALOG'];
		$configXml->config[3]->value = $_POST['DIR_FS_DOCUMENT_ROOT'];
		$configXml->config[4]->value = $_POST['DB_SERVER'];
		$configXml->config[5]->value = $_POST['DB_SERVER_USERNAME'];
		$configXml->config[6]->value = $_POST['DB_SERVER_PASSWORD'];
		$configXml->config[7]->value = $_POST['DB_DATABASE'];
		$configXml->config[8]->value = $_POST['ENABLE_SSL'];
		$configXml->config[9]->value = $_POST['ENABLE_SSL_CATALOG'];
		$configXml->config[11]->value = $_POST['SYSTEM_VERSION'];
		$configXml->config[12]->value = $_POST['SYSTEM_UPGRADE_USERNAME'];
		$configXml->config[13]->value = $_POST['SYSTEM_UPGRADE_PASSWORD'];
		$configXml->config[14]->value = $_POST['SYSTEM_FTP_SERVER'];
		$configXml->config[15]->value = $_POST['SYSTEM_FTP_PATH'];
		$configXml->config[16]->value = $_POST['SYSTEM_FTP_USERNAME'];
		$configXml->config[17]->value = $_POST['SYSTEM_FTP_PASSWORD'];
		//$configXml->config[17]->value = $installDate;
		$configXml->asXml($extractPath . 'includes/configure.xml');
		$ftpCmd = ftp_chmod($ftpConn, octdec('0644'), 'includes/configure.xml');
		if (!$ftpCmd){
			die('Error chmod 644 ' . $extractPath . 'includes/configure.xml');
		}
		$ftpCmd = ftp_chmod($ftpConn, octdec('0777'), '.htaccess');
		if (!$ftpCmd){
			die('Error chmod 777 ' . $extractPath . '.htaccess');
		}
		$htaccessPath = $extractPath . '.htaccess';
		$htaccessFile = file_get_contents($htaccessPath);
		$htaccessFileContents = explode("\n", $htaccessFile);
		foreach($htaccessFileContents as $lineNum => $line){
			if (substr($line, 0, 11) == 'RewriteBase'){
				$htaccessFileContents[$lineNum] = 'RewriteBase /' . (substr($_POST['DIR_WS_CATALOG'], 0, 1) == '/' ? substr($_POST['DIR_WS_CATALOG'], 1) : $_POST['DIR_WS_CATALOG']);
			}
		}
		file_put_contents($htaccessPath, implode("\n", $htaccessFileContents));
		$ftpCmd = ftp_chmod($ftpConn, octdec('0644'), '.htaccess');
		if (!$ftpCmd){
			die('Error chmod 644 ' . $extractPath . '.htaccess');
		}
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

extract_archive();

ftp_close($ftpConn);

?>