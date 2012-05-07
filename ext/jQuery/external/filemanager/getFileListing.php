<?php
chdir('../../../../');
require('includes/application_top.php');

$dirs = array();
$files = array();
$returnArr = array();
$ignoreFiles = array(
	'.htaccess',
	'thumbs.db'
);
function getFileTypeName($num, $ext = ''){
	$return = '';
	switch($num){
		case IMG_GIF: case IMAGETYPE_GIF: $return = 'GIF Image'; break;
		case IMG_JPG: case IMAGETYPE_JPG: $return = 'JPG Image'; break;
		case IMG_PNG: case IMAGETYPE_PNG: $return = 'PNG Image'; break;
		case IMG_WBMP: case IMAGETYPE_WBMP: $return = 'WBMP Image'; break;
		case IMG_XPM: case IMAGETYPE_XPM: $return = 'XPM Image'; break;
	}

	if (!empty($ext) && empty($return)){
		switch($ext){
			case 'dir': $return = 'File Folder'; break;
			case 'xml': $return = 'XML Document'; break;
			case 'rar': $return = 'RAR Archive'; break;
			case 'zip': $return = 'ZIP Archive'; break;
			case 'ttf': $return = 'TrueType Font File'; break;
			case 'pdf': $return = 'Adobe Acrobat Document'; break;
			case 'txt': $return = 'Text Document'; break;
			case 'css': $return = 'Cascading Style Sheet Document'; break;
			case 'js': $return = 'JScript Script File'; break;
			case 'psd': $return = 'Adobe Photoshop Image'; break;
			case 'doc': $return = 'Microsoft Word 97-2003 Document'; break;
			case 'docx': $return = 'Microsoft Word Document'; break;
			case 'xls': $return = 'Microsoft Excel 97-2003 Worksheet'; break;
			case 'xlsx': $return = 'Microsoft Excel Worksheet'; break;
			default: $return = strtoupper($ext) . ' File'; break;
		}
	}
	return $return;
}

if (is_dir($_POST['filesSource'])){
	$Dir = new DirectoryIterator($_POST['filesSource']);
	foreach($Dir as $dInfo){
		if ($dInfo->isDot() || in_array(strtolower($dInfo->getBasename()), $ignoreFiles)) {
			continue;
		}

		if ($dInfo->isDir()){
			$dirs[] = array(
				'name' => $dInfo->getBasename(),
				'created' => date('m/d/Y g:ia', $dInfo->getCTime()),
				'modified' => date('m/d/Y g:ia', $dInfo->getMTime()),
				'permissions' => substr(sprintf('%o', $dInfo->getPerms()), -4),
				'owner' => posix_getpwuid($dInfo->getOwner()),
				'type' => array(
					'icon' => 'ui-filemanager-icon-type-dir',
					'name' => getFileTypeName('', 'dir'),
					'mime' => 'directory'
				),
				'size' => array(
					'integer' => $dInfo->getSize(),
					'string' => round($dInfo->getSize()/1024, 2) . ' KB'
				),
				'path' => array(
					'relative' => str_replace(sysConfig::get('DIR_FS_DOCUMENT_ROOT'), '', $dInfo->getPathname()),
					'absolute' => $dInfo->getPathname()
				)
			);
		}else{
			$size = getimagesize($dInfo->getPathname());
			$files[] = array(
				'name' => $dInfo->getBasename(),
				'created' => date('m/d/Y g:ia', $dInfo->getCTime()),
				'modified' => date('m/d/Y g:ia', $dInfo->getMTime()),
				'dimensions' => $size[0] . ' X ' . $size[1],
				'permissions' => substr(sprintf('%o', $dInfo->getPerms()), -4),
				'owner' => posix_getpwuid($dInfo->getOwner()),
				'type' => array(
					'icon' => 'ui-filemanager-icon-type-' . $dInfo->getExtension(),
					'name' => getFileTypeName($size[2], $dInfo->getExtension()),
					'mime' => $size['mime']
				),
				'size' => array(
					'integer' => $dInfo->getSize(),
					'string' => round($dInfo->getSize()/1024, 2) . ' KB'
				),
				'path' => array(
					'relative' => str_replace(sysConfig::get('DIR_FS_DOCUMENT_ROOT'), '', $dInfo->getPathname()),
					'absolute' => $dInfo->getPathname()
				)
			);
		}
	}

	usort($dirs, function ($a, $b){
		return ($a['name'] > $b['name'] ? 1 : -1);
	});

	usort($files, function ($a, $b){
		return ($a['name'] > $b['name'] ? 1 : -1);
	});

	$returnArr = array_merge($dirs, $files);
}
$maxUpload = ini_get('upload_max_filesize');
if (!is_int($maxUpload)){
	if (substr($maxUpload, -1) == 'M'){
		$maxUpload = (int) (substr($maxUpload, 0, -1) * 1000) * 1024;
	}elseif (substr($maxUpload, -1) == 'K'){
		$maxUpload = (int) substr($maxUpload, 0, -1) * 1024;
	}elseif (substr($maxUpload, -1) == 'B'){
		$maxUpload = (int) substr($maxUpload, 0, -1);
	}
}

echo json_encode(array(
	'success' => true,
	'maxUpload' => $maxUpload,
	'files' => $returnArr
));
itwExit();
