<?php
class SesBrowserDetect
{

	private static $Expressions = array(
		'ENGINE_GECKO'   => array(
			'pattern'  => '/Gecko\/([0-9]+[\.0-9]*)/i',
			'Browsers' => array(
				'BROWSER_FIREFOX' => '/Firefox\/([0-9]+[\.0-9a-z]*)/i'
			)
		),
		'ENGINE_WEBKIT'  => array(
			'pattern'  => '/AppleWebKit\/([0-9]+[\.0-9]*)/i',
			'Browsers' => array(
				'BROWSER_CHROME'   => '/Chrome\/([0-9]+[\.0-9a-z]*)/i',
				'BROWSER_SAFARI'   => '/Version\/([0-9]+[\.0-9a-z]*)/i',
				'BROWSER_EPIPHANY' => '/Epiphany\/([0-9]+[\.0-9a-z]*)/i'
			)
		),
		'ENGINE_PRESTO'  => array(
			'pattern'  => '/Presto\/([0-9]+[\.0-9]*)/i',
			'Browsers' => array(
				'BROWSER_OPERA' => '/Version\/([0-9]+[\.0-9a-z]*)/i'
			)
		),
		'ENGINE_TRIDENT' => array(
			'pattern'  => '/Trident\/([0-9]+[\.0-9]*)/i',
			'Browsers' => array(
				'BROWSER_INTERNET_EXPLORER' => '/MSIE ([0-9]+[\.0-9a-z]*)/i'
			)
		)
	);

	private static $UserAgentInfo = array(
		'operatingSystem'      => 0,
		'browserEngine'        => 0,
		'browserEngineVersion' => 0,
		'browserName'          => 0,
		'browserVersion'       => 0
	);

	/**
	 * Platforms
	 */
	const PLATFORM_UNKNOWN = 0;

	const PLATFORM_LINUX = 1;

	const PLATFORM_WINDOWS = 2;

	const PLATFORM_MAC = 3;

	/**
	 * Engines
	 */
	const ENGINE_UNKNOWN = 0;

	const ENGINE_TRIDENT = 1;

	const ENGINE_GECKO = 2;

	const ENGINE_PRESTO = 3;

	const ENGINE_WEBKIT = 4;

	const ENGINE_VALIDATOR = 5;

	const ENGINE_ROBOTS = 6;

	/**
	 * Browsers
	 */
	const BROWSER_FIREFOX = 0;

	const BROWSER_CHROME = 1;

	const BROWSER_SAFARI = 2;

	const BROWSER_EPIPHANY = 3;

	const BROWSER_OPERA = 4;

	const BROWSER_INTERNET_EXPLORER = 5;

	public static function loadBrowserInfo() {
		$UserAgent = strtolower($_SERVER['HTTP_USER_AGENT']);

		if (strpos($UserAgent, 'linux')){
			self::$UserAgentInfo['operatingSystem'] = self::PLATFORM_LINUX;
		}
		elseif (strpos($UserAgent, 'mac')) {
			self::$UserAgentInfo['operatingSystem'] = self::PLATFORM_MAC;
		}
		elseif (strpos($UserAgent, 'win')) {
			self::$UserAgentInfo['operatingSystem'] = self::PLATFORM_WINDOWS;
		}

		foreach(self::$Expressions as $EngineName => $eInfo){
			$matches = array();
			preg_match($eInfo['pattern'], $UserAgent, &$matches);
			if (!empty($matches)){
				self::$UserAgentInfo['browserEngine'] = constant('self::' . $EngineName);
				self::$UserAgentInfo['browserEngineVersion'] = (float)$matches[1];

				foreach($eInfo['Browsers'] as $BrowserName => $pattern){
					$matches = array();
					preg_match($pattern, $UserAgent, &$matches);
					if (!empty($matches)){
						self::$UserAgentInfo['browserName'] = constant('self::' . $BrowserName);
						self::$UserAgentInfo['browserVersion'] = (float)$matches[1];
						break;
					}
				}
				break;
			}
		}

		if (self::$UserAgentInfo['browserEngine'] == self::ENGINE_UNKNOWN){
			if (
				strpos($UserAgent, 'robot') ||
				strpos($UserAgent, 'spider') ||
				strpos($UserAgent, 'bot') ||
				strpos($UserAgent, 'crawl') ||
				strpos($UserAgent, 'search')
			){
				self::$UserAgentInfo['browserEngine'] = self::ENGINE_ROBOTS;
			}
			elseif (
				strpos($UserAgent, 'w3c_validator') ||
				strpos($UserAgent, 'jigsaw')
			) {
				self::$UserAgentInfo['browserEngine'] = self::ENGINE_VALIDATOR;
			}
		}

		if (self::$UserAgentInfo['browserEngine'] == self::ENGINE_TRIDENT && self::$UserAgentInfo['browserVersion'] > 7 && self::$UserAgentInfo['browserVersion'] < 9){
			echo '<div class="ui-messageStack ui-messageStack-info">' .
				'<div class="ui-messageStack-message ui-messageStack-info ui-corner-all">' .
				'<span class="ui-messageStack-message-icon ui-icon ui-icon-info"></span>' .
				'<span class="ui-messageStack-message-text">For a better experience please upgrade your browser, or click to download another supported browser from the list below<br><br>' .
				'<span style="display:inline-block;margin:10px;"><a href="http://www.google.com/chrome" style="text-decoration:none;" target="_blank"><img style="border:0;" src="' . sysConfig::getDirWsAdmin() . 'images/SupportedBrowsers/chrome.png"></a></span>' .
				'<span style="display:inline-block;margin:10px;"><a href="http://www.opera.com/download" style="text-decoration:none;" target="_blank"><img style="border:0;" src="' . sysConfig::getDirWsAdmin() . 'images/SupportedBrowsers/opera.png"></a></span>' .
				'<span style="display:inline-block;margin:10px;"><a href="http://www.apple.com/safari/download" style="text-decoration:none;" target="_blank"><img style="border:0;" src="' . sysConfig::getDirWsAdmin() . 'images/SupportedBrowsers/safari.png"></a></span>' .
				'<span style="display:inline-block;margin:10px;"><a href="http://www.mozilla.org/en-US/firefox/new/" style="text-decoration:none;" target="_blank"><img style="border:0;" src="' . sysConfig::getDirWsAdmin() . 'images/SupportedBrowsers/firefox.png"></a></span>' .
				'</span>' .
				'</div>' .
				'</div>';
		}
		elseif (self::$UserAgentInfo['browserEngine'] == self::ENGINE_UNKNOWN){
			$matches = array();
			preg_match(self::$Expressions['ENGINE_TRIDENT']['Browsers']['BROWSER_INTERNET_EXPLORER'], $UserAgent, &$matches);
			if (!empty($matches)){
				$version = (float) $matches[1];
				if ($version <= 7){
					echo '<div style="text-align:center;">
					<img src="' . sysConfig::getDirWsAdmin() . 'images/seslogo.png">
					<br><br>
					<img src="' . sysConfig::getDirWsAdmin() . 'images/SupportedBrowsers/no_ie7.png">
					<br><br>
					<span>Sales Igniter Software No Longer Supports IE7, Please Download A Supported Browser Below.</span>
					<br><br>
					<span style="display:inline-block;margin:10px;"><a href="http://www.google.com/chrome" style="text-decoration:none;" target="_blank"><img style="border:0;" src="' . sysConfig::getDirWsAdmin() . 'images/SupportedBrowsers/chrome.png"><br>Latest Chrome</a></span>
					<span style="display:inline-block;margin:10px;"><a href="http://www.opera.com/download" style="text-decoration:none;" target="_blank"><img style="border:0;" src="' . sysConfig::getDirWsAdmin() . 'images/SupportedBrowsers/opera.png"><br>Latest Opera</a></span>
					<span style="display:inline-block;margin:10px;"><a href="http://www.apple.com/safari/download" style="text-decoration:none;" target="_blank"><img style="border:0;" src="' . sysConfig::getDirWsAdmin() . 'images/SupportedBrowsers/safari.png"><br>Latest Safari</a></span>
					<span style="display:inline-block;margin:10px;"><a href="http://www.mozilla.org/en-US/firefox/new/" style="text-decoration:none;" target="_blank"><img style="border:0;" src="' . sysConfig::getDirWsAdmin() . 'images/SupportedBrowsers/firefox.png"><br>Latest Firefox</a></span>
					<span style="display:inline-block;margin:10px;"><a href="http://windows.microsoft.com/en-US/internet-explorer/downloads/ie" style="text-decoration:none;" target="_blank"><img style="border:0;" src="' . sysConfig::getDirWsAdmin() . 'images/SupportedBrowsers/ie.png"><br>Latest Internet Explorer</a></span>
					</div>';
					exit;
				}
			}
			mail('stephen@itwebexperts.com', 'Unknown User Agent Found (' . sysConfig::get('HTTP_HOST') . ')', $_SERVER['HTTP_USER_AGENT']);
		}
	}

	public static function isWebkit(){
		return (self::$UserAgentInfo['browserEngine'] == self::ENGINE_WEBKIT);
	}

	public static function isTrident(){
		return (self::$UserAgentInfo['browserEngine'] == self::ENGINE_TRIDENT);
	}

	public static function isGecko(){
		return (self::$UserAgentInfo['browserEngine'] == self::ENGINE_GECKO);
	}

	public static function isPresto(){
		return (self::$UserAgentInfo['browserEngine'] == self::ENGINE_PRESTO);
	}

	public static function isIE() {
		return (self::$UserAgentInfo['browserName'] == self::BROWSER_INTERNET_EXPLORER);
	}

	public static function isFirefox() {
		return (self::$UserAgentInfo['browserName'] == self::BROWSER_FIREFOX);
	}

	public static function isChrome() {
		return (self::$UserAgentInfo['browserName'] == self::BROWSER_CHROME);
	}

	public static function isSafari() {
		return (self::$UserAgentInfo['browserName'] == self::BROWSER_SAFARI);
	}

	public static function isOpera() {
		return (self::$UserAgentInfo['browserName'] == self::BROWSER_SAFARI);
	}

	public static function getOperatingSystem() {
		return self::$UserAgentInfo['operatingSystem'];
	}

	public static function getBrowserEngine() {
		return self::$UserAgentInfo['browserEngine'];
	}

	public static function getBrowserEngineVersion() {
		return self::$UserAgentInfo['browserEngineVersion'];
	}

	public static function getBrowser() {
		return self::$UserAgentInfo['browserName'];
	}

	public static function getBrowserVersion() {
		return self::$UserAgentInfo['browserVersion'];
	}

	public static function isHtml5() {
		return false;
	}

	public static function isMinEngineVersion($Version) {
		return (self::getBrowserEngineVersion() >= $Version);
	}

	public static function isMinBrowserVersion($Version) {
		return (self::getBrowserVersion() >= $Version);
	}

	public static function isMaxEngineVersion($Version) {
		return (self::getBrowserEngineVersion() <= $Version);
	}

	public static function isMaxBrowserVersion($Version) {
		return (self::getBrowserVersion() <= $Version);
	}
}