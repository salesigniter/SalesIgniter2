<?php
class SystemCacheFile {
	private $data = array();

	public function __construct($key, $path){
		$this->key = str_replace(' ', '-', $key);
		$this->fileName = $this->key . '.cache';
		$this->realPath = $path;

		$filename = $this->realPath . $this->fileName;
		if (file_exists($filename) && is_readable($filename)){
			$data = file_get_contents($filename);
			$this->data = @unserialize($data);
		}
	}

	public function setData($key, $data){
		$this->data[$key] = $data;
	}

	public function hasData($key){
		if (isset($this->data['addedHeaders'][$key])){
			return true;
		}
		elseif (isset($this->data[$key])){
			return true;
		}
		return false;
	}

	public function hasExpired(){
		if ($this->hasData('Expires') === true){
			$Expires = DateTime::createFromFormat('D, d M Y H:i:s \G\M\T', $this->getData('Expires'));
			return (time() > $Expires->getTimestamp());
		}
		return true;
	}

	public function getData($key){
		if (isset($this->data['addedHeaders'][$key])){
			return $this->data['addedHeaders'][$key];
		}
		elseif (isset($this->data[$key])){
			return $this->data[$key];
		}
		return false;
	}

	public function save($includeCacheInfo = true){
		$file = fopen($this->realPath . $this->fileName,'w');
		if (!$file) throw new Exception('Could not write to cache');
		// Serializing along with the TTL
		if ($includeCacheInfo === true){
			$data = serialize($this->data);
		}else{
			$data = $this->data['content'];
		}
		if (fwrite($file, $data)===false) {
			throw new Exception('Could not write to cache');
		}
		fclose($file);
	}

	public function clear(){
		if (file_exists($this->realPath . $this->fileName)){
			unlink($this->realPath . $this->fileName);
		}
	}
}

class SystemCacheApc {

}

class SystemCacheMemcache {

}

class SystemCache {
	private $cacheDriver = 'file';
	private $cacheKey = false;
	private $cachePath = false;
	private $CacheClass = false;
	private $addedHeaders = array();

	public function __construct($key = '', $path = '', $driver = 'file'){
		if (!empty($key)){
			$this->cacheKey = $key;
		}else{
			$this->cacheKey = md5('no-key-' . $_SERVER['REQUEST_URI'] . '-' . $_SERVER['QUERY_STRING']);
		}

		if (!empty($path)){
			$this->cachePath = realpath(dirname(__FILE__) . '/../../') . '/' . $path;
		}else{
			$this->cachePath = realpath(dirname(__FILE__) . '/../../') . '/cache/';
		}

		$this->cacheDriver = $driver;
		$className = 'SystemCache' . ucfirst($this->cacheDriver);
		$this->CacheClass = new $className($this->cacheKey, $this->cachePath);
	}

	public function setDriver($driver){
		$this->cacheDriver = $driver;
	}

	public function setAddedHeaders($headers){
		$this->CacheClass->setData('addedHeaders', $headers);
	}

	public function setContentType($type){
		$this->CacheClass->setData('contentType', $type);
	}

	public function setExpires($time){
		$this->CacheClass->setData('expires', $time);
	}

	public function setKey($key){
		$this->CacheClass->setData('key', $key);
	}

	public function setPath($path){
		$this->CacheClass->setData('path', $path);
	}

	public function setContent($content){
		$this->CacheClass->setData('content', $content);
	}

	public function setLastModified($time){
		$this->CacheClass->setData('lastModified', $time);
	}

	public function loadData(){
		if ($this->CacheClass->hasExpired() === true){
			$this->CacheClass->clear();
			return false;
		}
		return true;
	}

	public function store($includeCacheInfo = true){
		$this->CacheClass->save($includeCacheInfo);
	}

	public function output($return = false, $wHeaders = false){
		if ($wHeaders === true){
			$this->serveHeaders();
		}

		if ($return === false){
			echo $this->CacheClass->getData('content');
			return null;
		}
		return $this->CacheClass->getData('content');
	}

	public function clear($key, $path = ''){
		$className = 'SystemCache' . ucfirst($this->cacheDriver);
		$Cache = new $className($key, $path);
		$Cache->clear();
	}

	private function serveHeaders(){
		header("Cache-Control: public");
		header('Content-Length: ' . strlen($this->CacheClass->getData('content')));

		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
		//echo $_SERVER['HTTP_IF_MODIFIED_SINCE'] . ' :: ' . strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) . '<br>';
			if ($this->CacheClass->hasData('Last-Modified')){
				$ServerLastModified = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
				if (substr($ServerLastModified, -1) == ';'){
					$ServerLastModified = substr($ServerLastModified, 0, -1);
				}
				//echo $this->CacheClass->getData('Last-Modified') . ' :: ' . strtotime($this->CacheClass->getData('Last-Modified'));
				$modifiedCheck = DateTime::createFromFormat('D, d M Y H:i:s \G\M\T', $ServerLastModified);
				$lastModified = DateTime::createFromFormat('D, d M Y H:i:s \G\M\T', $this->CacheClass->getData('Last-Modified'));
				if ($modifiedCheck->getTimestamp() >= $lastModified->getTimestamp()){
					header('Cache-File-Name: ' . $this->CacheClass->fileName);
					header('Last-Modified: ' . $lastModified->format('D, d M Y H:i:s \G\M\T'), true, 304);
					exit;
				}
			}
		}

		if ($this->CacheClass->hasData('addedHeaders')){
			//echo '<pre>';print_r($this->CacheClass->getData('addedHeaders'));
			foreach($this->CacheClass->getData('addedHeaders') as $k => $v){
				if ($k != 'Content-Length'){
					header($k . ': ' . $v . ';');
				}
			}
		}
	}
}