<?php
/*
 * Sales Igniter E-Commerce System
 * Version: 2.0
 *
 * I.T. Web Experts
 * http://www.itwebexperts.com
 *
 * Copyright (c) 2011 I.T. Web Experts
 *
 * This script and its source are not distributable without the written conscent of I.T. Web Experts
 */

/**
 *
 */
class sysCurrency
{

	/**
	 * @var array
	 */
	private static $currencies = array();

	/**
	 * @var array
	 */
	private static $currency = array();

	/**
	 * @static
	 * @param string $currency
	 */
	public static function init($currency = '')
	{
		self::getCurrencies();

		$useLanguageCurrency = sysConfig::get('USE_DEFAULT_LANGUAGE_CURRENCY');
		$systemDefaultCurrency = sysConfig::get('DEFAULT_CURRENCY');
		$languageDefaultCurrency = sysLanguage::getCurrency();
		if (Session::exists('currency') === false || isset($_GET['currency']) || !empty($currency) || ($useLanguageCurrency == 'true' && $languageDefaultCurrency != Session::get('currency'))){
			if (isset($_GET['currency']) && !empty($_GET['currency'])){
				$currency = $_GET['currency'];
			}

			if (self::exists($currency) === false){
				$currency = ($useLanguageCurrency == 'true') ? $languageDefaultCurrency : $systemDefaultCurrency;
			}

			self::setCurrency($currency);

			Session::set('currency', self::$currency['code']);
			Session::set('currency_value', self::$currency['value']);
		}
	}

	/**
	 * @static
	 * @param bool $reload
	 * @return array
	 */
	public static function getCurrencies($reload = false)
	{
		if (empty(self::$currencies) || $reload === true){
			$Currencies = Doctrine_Manager::getInstance()
				->getCurrentConnection()
				->fetchAssoc('select * from currencies');
			if (sizeof($Currencies) > 0){
				foreach($Currencies as $cInfo){
					self::$currencies[$cInfo['code']] = array(
						'id'              => $cInfo['currencies_id'],
						'code'            => $cInfo['code'],
						'title'           => $cInfo['title'],
						'symbol_left'     => $cInfo['symbol_left'],
						'symbol_right'    => $cInfo['symbol_right'],
						'decimal_point'   => $cInfo['decimal_point'],
						'thousands_point' => $cInfo['thousands_point'],
						'decimal_places'  => $cInfo['decimal_places'],
						'value'           => $cInfo['value']
					);
				}
			}
		}
		return self::$currencies;
	}

	/**
	 * @static
	 * @param $code
	 * @return array
	 */
	public static function findCurrency($code)
	{
		if ($code != ''){
			$currency = self::getCurrency($code);
		}
		else {
			$currency = self::$currency;
		}
		return $currency;
	}

	/**
	 * @static
	 * @param int $code
	 * @return array
	 */
	public static function getCurrency($code = 0)
	{
		$currencies = self::getCurrencies(true);
		$currency = array();
		foreach($currencies as $cInfo){
			if ($cInfo['code'] == $code){
				$currency = $cInfo;
				break;
			}
		}

		return $currency;
	}

	/**
	 * @static
	 * @param int $code
	 * @return int
	 */
	public static function getId($code = 0)
	{
		$currency = self::findCurrency($code);
		return $currency['id'];
	}

	/**
	 * @static
	 * @param string $code
	 * @return string
	 */
	public static function getTitle($code = '')
	{
		$currency = self::findCurrency($code);
		return $currency['title'];
	}

	/**
	 * @static
	 * @param string $code
	 * @return mixed
	 */
	public static function getSymbolLeft($code = '')
	{
		$currency = self::findCurrency($code);
		return $currency['symbol_left'];
	}

	/**
	 * @static
	 * @param string $code
	 * @return mixed
	 */
	public static function getSymbolRight($code = '')
	{
		$currency = self::findCurrency($code);
		return $currency['symbol_right'];
	}

	/**
	 * @static
	 * @param string $code
	 * @return mixed
	 */
	public static function getDecimalPoint($code = '')
	{
		$currency = self::findCurrency($code);
		return $currency['decimal_point'];
	}

	/**
	 * @static
	 * @param string $code
	 * @return mixed
	 */
	public static function getThousandsPoint($code = '')
	{
		$currency = self::findCurrency($code);
		return $currency['thousands_point'];
	}

	/**
	 * @static
	 * @param string $code
	 * @return mixed
	 */
	public static function getDecimalPlaces($code = '')
	{
		$currency = self::findCurrency($code);
		return $currency['decimal_places'];
	}

	/**
	 * @static
	 * @param string $code
	 * @return mixed
	 */
	public static function getValue($code = '')
	{
		$currency = self::findCurrency($code);
		return $currency['value'];
	}

	public function getCode(){
		return self::$currency['code'];
	}

	/**
	 * @static
	 * @param string $key
	 * @return bool
	 */
	public static function exists($key)
	{
		return isset(self::$currencies[$key]);
	}

	/**
	 * @static
	 * @param string $key
	 * @return array
	 */
	public static function get($key)
	{
		global $messageStack;
		if (self::exists($key)){
			$currency = self::$currencies[$key];
		}
		else {
			trigger_error('Currency key not available', E_USER_NOTICE);
			debug_print_backtrace();
			/*$messageStack->addSession('footerStack', array(
								'Server Message' => 'Language key not defined',
								'Key Requested' => $key
							), 'error');*/
		}
		return $currency;
	}

	/**
	 * @static
	 * @param string $code
	 */
	public static function setCurrency($code)
	{
		self::$currency = self::$currencies[$code];
	}

	/**
	 * @static
	 * @param     $price
	 * @param     $tax
	 * @param int $quantity
	 * @return string
	 */
	public static function displayPrice($price, $tax, $quantity = 1)
	{
		return self::format(tep_add_tax($products_price, $products_tax) * $quantity);
	}

	/**
	 * @static
	 * @param        $number
	 * @param bool   $calculate_value
	 * @param string $code
	 * @param string $value
	 * @return string
	 */
	public static function format($number, $calculate_value = true, $code = '', $value = '')
	{
		if (empty($code)){
			$code = Session::get('currency');
		}

		$useCurrency = self::$currencies[$code];

		$symbolLeft = $useCurrency['symbol_left'];
		$symbolRight = $useCurrency['symbol_right'];
		$decimalPlaces = $useCurrency['decimal_places'];
		$decimalPoint = $useCurrency['decimal_point'];
		$thousandsPoint = $useCurrency['thousands_point'];

		$rate = 1;
		if ($calculate_value == true){
			$rate = (!empty($value) ? $value : $useCurrency['value']);
			$number = $number * $rate;

			/*
			* if the selected currency is in the european euro-conversion and the default currency is euro,
			* the currency will displayed in the national currency and euro currency
			*/
			$checkArr = array(
				'DEM', 'BEF', 'LUF', 'ESP', 'FRF', 'IEP', 'ITL', 'NLG', 'ATS', 'PTE', 'FIM', 'GRD'
			);
			if (sysConfig::get('DEFAULT_CURRENCY') == 'EUR' && in_array($code, $checkArr)){
				$symbolRight .= ' <small>[' . self::format($number, true, 'EUR') . ']</small>';
			}
		}

		$format_string = $symbolLeft . number_format(
			tep_round($number, $decimalPlaces),
			$decimalPlaces,
			$decimalPoint,
			$thousandsPoint
		) . $symbolRight;
		return $format_string;
	}
}
