<?php
set_time_limit(0);
require('includes/application_top.php');

/* backup the db OR just a table */
function backup_tables($host,$user,$pass,$name,$tables = '*')
{

	$link = mysql_connect($host,$user,$pass);
	mysql_select_db($name,$link);
	$return = '';
	//get all of the tables
	if($tables == '*')
	{
		$tables = array();
		$result = mysql_query('SHOW TABLES');
		while($row = mysql_fetch_row($result))
		{
			$tables[] = $row[0];
		}
	}
	else
	{
		$tables = is_array($tables) ? $tables : explode(',',$tables);
	}

	//cycle through
	foreach($tables as $table)
	{
		$result = mysql_query('SELECT * FROM '.$table);
		$num_fields = mysql_num_fields($result);

		$return.= 'DROP TABLE '.$table.';';
		$row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
		$return.= "\n\n".$row2[1].";\n\n";

		for ($i = 0; $i < $num_fields; $i++)
		{
			while($row = mysql_fetch_row($result))
			{
				$return.= 'INSERT INTO '.$table.' VALUES(';
				for($j=0; $j<$num_fields; $j++)
				{
					$row[$j] = addslashes($row[$j]);
					$row[$j] = ereg_replace("\n","\\n",$row[$j]);
					if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
					if ($j<($num_fields-1)) { $return.= ','; }
				}
				$return.= ");\n";
			}
		}
		$return.="\n\n\n";
	}

	//save file
	$fileName = sysConfig::getDirFsCatalog().'backupDB/db-backup-'.time().'-'.(md5(implode(',',$tables))).'.sql';
	$handle = fopen($fileName,'w+');
	fwrite($handle,$return);
	fclose($handle);

}

function backup_files(){
	//zip all files from server and move them in backups folder with time() name

	$tmpFile = sysConfig::getDirFsCatalog(). 'backupFiles/bk_' . date('Ymdhis') . '.zip';

	$UpdateZip = new ZipArchive();
	if ($UpdateZip->open($tmpFile, ZipArchive::CREATE) !== TRUE){
		die('Error Creating Temporary Zip File.');
	}

	$copyDir = sysConfig::getDirFsCatalog();

	$RootDir = new RecursiveDirectoryIterator($copyDir);
	$Files = new RecursiveIteratorIterator($RootDir, RecursiveIteratorIterator::SELF_FIRST);

	$ignore = array();
	$ignore[] = 'backupFiles';
	$ignore[] = 'backupDB';
	$ignore[] = 'images';

	// Process all files and folders and add them to the zip file
	foreach ($Files as $File){
		if ($File->getBasename() == '.' || $File->getBasename() == '..') continue;

		$fullPath = $File->getPathname();
		$process = true;
		$cleaned = substr($fullPath, strlen($copyDir));
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
			$internalPath = str_replace($copyDir, '', $fullPath);
			if ($File->isDir() === true){
				$UpdateZip->addEmptyDir($internalPath . '/');
			}elseif ($File->isFile() === true){
				$UpdateZip->addFile($fullPath, $internalPath);
			}
		}
	}

	$UpdateZip->close();

}


function isMasterPassword($password){
	$RequestObj = new CurlRequest('https://' . sysConfig::get('SYSTEM_UPGRADE_SERVER') . '/sesUpgrades/getPassword.php');
	$RequestObj->setSendMethod('post');
	$RequestObj->setData(array(
			'clientPassword' => $password,
			'username' => sysConfig::get('SYSTEM_UPGRADE_USERNAME'),
			'password' => sysConfig::get('SYSTEM_UPGRADE_PASSWORD'),
			'domain' => sysConfig::get('HTTP_HOST')
		));

	$ResponseObj = $RequestObj->execute();

	$json = json_decode($ResponseObj->getResponse());
	if ($json->success === true){
		return true;
	}
	return false;
}

function logMessage($message){
	global $ftpConn;
	$myFile = sysConfig::getDirFsCatalog(). "file_log.txt";

	if(!file_exists($myFile)){

		$temp = tempnam(sys_get_temp_dir(), 'ses');
		//echo $temp;
		$ftpCmd = ftp_put($ftpConn, 'file_log.txt', $temp, FTP_ASCII);
		if (!$ftpCmd){
			die('Error1');
		}
		$ftpCmd = ftp_chmod($ftpConn, octdec('0777'), 'file_log.txt');
		if (!$ftpCmd){
			die('Error2');
		}
	}
	$fh = fopen($myFile, 'a') or die("can't open file");
	fwrite($fh, date('Y-m-d H:i:s').': '.$message."\n");
	fclose($fh);
}

$ftpConn = ftp_connect(sysConfig::get('SYSTEM_FTP_SERVER'));
if ($ftpConn === false){
	die('Error ftp_connect');
}
else {
	$ftpCmd = ftp_login($ftpConn,sysConfig::get('SYSTEM_FTP_USERNAME') , sysConfig::get('SYSTEM_FTP_PASSWORD'));
	if (!$ftpCmd){
		die('Error ftp_login');
	}
}

if(sysConfig::exists('SYSTEM_FTP_PATH') == false){
	$ftpPath = '/public_html/';
}else{
	$ftpPath = sysConfig::get('SYSTEM_FTP_PATH');
}

$ftpCmd = ftp_chdir($ftpConn, $ftpPath);
if (!$ftpCmd){
	die('Error ftp_chdir public_html');
}

//if(isMasterPassword($_POST['password'])){
	ftp_mkdir($ftpConn,'backupFiles');
	ftp_mkdir($ftpConn,'backupDB');
	$ftpCmd = ftp_chmod($ftpConn, octdec('0777'), 'backupFiles');
	if (!$ftpCmd){
		die('Error chmod 777 ' . 'backupFiles');
	}
	$ftpCmd = ftp_chmod($ftpConn, octdec('0777'), 'backupDB');
	if (!$ftpCmd){
		die('Error chmod 777 ' . 'backupDB');
	}
	backup_files();
	backup_tables('localhost',sysConfig::get('DB_SERVER_USERNAME'),sysConfig::get('DB_SERVER_PASSWORD'),sysConfig::get('DB_DATABASE'));
	ftp_close($ftpConn);
	$json = array(
		'success' => true,
		'catalogPath' => sysConfig::getDirFsCatalog(),
		'DIR_WS_CATALOG' => sysConfig::get('DIR_WS_CATALOG'),
		'SYSTEM_VERSION' => sysConfig::get('SYSTEM_VERSION'),
		'SYSTEM_UPGRADE_USERNAME' => sysConfig::get('SYSTEM_UPGRADE_USERNAME'),
		'SYSTEM_UPGRADE_PASSWORD' => sysConfig::get('SYSTEM_UPGRADE_PASSWORD'),
		'DIR_FS_DOCUMENT_ROOT' => sysConfig::get('DIR_FS_DOCUMENT_ROOT'),
		'HTTP_DOMAIN_NAME' => sysConfig::get('HTTP_DOMAIN_NAME'),
		'HTTPS_DOMAIN_NAME' => sysConfig::get('HTTPS_DOMAIN_NAME'),
		'DB_SERVER' => sysConfig::get('DB_SERVER'),
		'DB_SERVER_USERNAME' => sysConfig::get('DB_SERVER_USERNAME'),
		'DB_SERVER_PASSWORD' => sysConfig::get('DB_SERVER_PASSWORD'),
		'DB_DATABASE' => sysConfig::get('DB_DATABASE'),
		'ENABLE_SSL' => sysConfig::get('ENABLE_SSL'),
		'ENABLE_SSL_CATALOG' => sysConfig::get('ENABLE_SSL_CATALOG'),
		'SYSTEM_FTP_SERVER' => sysConfig::get('SYSTEM_FTP_SERVER'),
		'SYSTEM_FTP_PATH' => $ftpPath,
		'SYSTEM_FTP_USERNAME' => sysConfig::get('SYSTEM_FTP_USERNAME'),
		'SYSTEM_FTP_PASSWORD' => sysConfig::get('SYSTEM_FTP_PASSWORD')
	);

	require('includes/application_bottom.php');
	header('Content-Type: text/json');
	echo json_encode($json);
	exit;
//}



?>