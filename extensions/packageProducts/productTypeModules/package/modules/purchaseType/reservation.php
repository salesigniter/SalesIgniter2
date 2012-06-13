<?php
class PackagePurchaseTypeReservation
{

	public static function getPackagedTableHeaders()
	{
		return array(
			array('id' => 'insurance_value', 'text' => 'Insurance Value'),
			array('id' => 'insurance_cost', 'text' => 'Insurance Cost')
		);
	}

	public static function getPackagedTableBody($pInfo)
	{
		global $currencies;
		$PayPerRental = Doctrine_Core::getTable('ProductsPayPerRental')
			->findOneByProductsId($pInfo['id']);

		return array(
			array('text' => $currencies->format($PayPerRental->insurance_value)),
			array('text' => $currencies->format($PayPerRental->insurance_cost))
		);
	}

	public static function getSettingsAddToPackage(PurchaseType_reservation $PurchaseType)
	{
		$Fields = array(
			array(
				'colspan' => 2,
				'label'   => '<b>Product Pricing</b>'
			)
		);
		foreach(PurchaseType_reservation_utilities::getRentalPricing($PurchaseType->getPayPerRentalId()) as $iPrices){
			$pprId = $iPrices['pay_per_rental_id'];
			$priceId = $iPrices['pay_per_rental_types_id'];
			$numberOf = $iPrices['number_of'];
			$productId = $PurchaseType->getProductId();

			$PriceField = htmlBase::newElement('input')
				->attr('size', 6)
				->setName('settings[' . $productId . '][price][' . $pprId . '][' . $priceId . '][' . $numberOf . ']')
				->val($iPrices['price']);

			$OverrideField = htmlBase::newElement('checkbox')
				->val(1)
				->setName('settings[' . $productId . '][override_price][' . $pprId . '][' . $priceId . '][' . $numberOf . ']')
				->setLabel('Click To Override')
				->setLabelPosition('after');

			$Fields[] = array(
				'label' => $iPrices['PricePayPerRentalPerProductsDescription'][0]['price_per_rental_per_products_name'],
				'field' => $PriceField->draw() . $OverrideField->draw()
			);
		}

		return $Fields;
	}

	public static function addPackageRowData(PurchaseType_reservation $PurchaseType, $sInfo, &$NewRow)
	{
		$hiddenField = '';
		if (isset($sInfo['override_price'])){
			$showPrice = '';
			foreach(PurchaseType_reservation_utilities::getRentalPricing($PurchaseType->getPayPerRentalId()) as $iPrices){
				$pprId = $iPrices['pay_per_rental_id'];
				$priceId = $iPrices['pay_per_rental_types_id'];
				$numberOf = $iPrices['number_of'];

				$showPrice .= $iPrices['PricePayPerRentalPerProductsDescription'][0]['price_per_rental_per_products_name'] . ': ';
				if (isset($sInfo['override_price'][$pprId][$priceId][$numberOf])){
					$showPrice .= $sInfo['price'][$pprId][$priceId][$numberOf];
					$hiddenField .= '<input type="hidden" name="package_product_settings[' . $PurchaseType->getProductId() . '][price][' . $pprId . '][' . $priceId . '][' . $numberOf . ']" value="' . $sInfo['price'][$pprId][$priceId][$numberOf] . '">';
				}
				else {
					$showPrice .= 'Period Current Price';
				}
				$showPrice .= '<br>';
			}
		}
		else {
			$showPrice = 'Products Current Period Prices';
		}

		$NewRow['columns'][] = array(
			'text' => $hiddenField . $showPrice
		);
	}
}