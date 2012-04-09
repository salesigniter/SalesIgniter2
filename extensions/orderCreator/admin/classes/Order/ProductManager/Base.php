<?php
/**
 * Product manager class for the order creator
 *
 * @package OrderCreator
 * @author Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

require(dirname(__FILE__) . '/Product.php');

class OrderCreatorProductManager extends OrderProductManager implements Serializable
{

	/**
	 * @var OrderCreatorProduct[]
	 */
	public $Contents = array();

	/**
	 * @param array|null $orderedProducts
	 */
	public function __construct(array $orderedProducts = null) {
		if (is_null($orderedProducts) === false){
			foreach($orderedProducts as $i => $pInfo){
				$orderedProduct = new OrderCreatorProduct($pInfo);
				while(array_key_exists($orderedProduct->getId(), $this->Contents)){
					$orderedProduct->regenerateId();
				}

				$this->Contents[$orderedProduct->getId()] = $orderedProduct;
			}
			$this->cleanUp();
		}
	}

	/**
	 * @return string
	 */
	public function serialize() {
		$data = array(
			'orderId'  => $this->orderId,
			'Contents' => $this->Contents
		);
		return serialize($data);
	}

	/**
	 * @param string $data
	 */
	public function unserialize($data) {
		$data = unserialize($data);
		foreach($data as $key => $dInfo){
			$this->$key = $dInfo;
		}
	}

	/**
	 * @param int $id
	 * @return OrderCreatorProduct|bool
	 */
	public function get($id) {
		$OrderedProduct = parent::get((int) $id);
		return $OrderedProduct;
	}

	/**
	 *
	 */
	public function init(){
		foreach($this->getContents() as $OrderedProduct){
			$OrderedProduct->init();
		}
	}

	/**
	 *
	 */
	public function updateFromPost() {
		global $currencies, $Editor;
		foreach($_POST['product'] as $id => $pInfo){
			if (!isset($pInfo['qty'])){
				continue;
			}

			$Product = $this->get($id);
			if (is_null($Product)){
				die('Error: A Product Was Posted That Was Not In The Product Manager. ( ID:' . $id . ' )');
			}

			$Product->setQuantity($pInfo['qty']);
			$Product->setPrice($pInfo['price']);
			$Product->setTaxRate($pInfo['tax_rate']);

			if (isset($pInfo['barcode_id'])){
				$barcodes = array();
				foreach($pInfo['barcode_id'] as $bID){
					$barcodes[] = array(
						'barcode_id' => $bID
					);
				}
				$Product->setBarcodes($barcodes);
			}

			if (isset($pInfo['attributes'])){
				$Product->updateInfo(array(
					'attributes' => $pInfo['attributes']
				));
			}

			$ProductType = $Product->getProductTypeClass();
			if (method_exists($ProductType, 'OrderCreatorProductManagerUpdateFromPost')){
				$ProductType->OrderCreatorProductManagerUpdateFromPost($Product);
			}
			/*
   product[85544][qty]:1
   product[85544][purchase_type]:new
   product[85544][attributes][1][value]:2
   product[85544][attributes][1][prefix]:
   product[85544][attributes][1][price]:0
   product[85544][tax_rate]:0
   product[85544][price]:17.99
   */
		}
	}

	/**
	 * @param Doctrine_Collection $CollectionObj
	 */
	public function addAllToCollection(Doctrine_Collection $CollectionObj) {
		$CollectionObj->clear();
		foreach($this->Contents as $id => $Product){
			$OrderedProduct = new OrdersProducts();

			$OrderedProduct->products_id = $Product->getProductsId();
			$OrderedProduct->products_quantity = $Product->getQuantity();
			$OrderedProduct->products_name = $Product->getName();
			$OrderedProduct->products_model = $Product->getModel();
			$OrderedProduct->products_price = $Product->getFinalPrice(false, false);
			$OrderedProduct->final_price = $Product->getFinalPrice(false, false);
			$OrderedProduct->products_tax = $Product->getTaxRate();

			$Product->onAddToCollection($OrderedProduct);

			$CollectionObj->add($OrderedProduct);
		}
	}

	/**
	 * @param OrderCreatorProduct $orderedProduct
	 */
	public function add(OrderCreatorProduct &$orderedProduct) {
		$addAllowed = true;
		if (method_exists($orderedProduct, 'OrderCreatorAllowAddToContents')){
			$addAllowed = $orderedProduct->OrderCreatorAllowAddToContents();
		}

		if ($addAllowed === true){
			$orderedProduct->regenerateId();
			while(array_key_exists($orderedProduct->getId(), $this->Contents)){
				$orderedProduct->regenerateId();
			}

			if ($orderedProduct->getPrice() <= 0){
				$orderedProduct->OrderCreatorOnAddToContents();
			}
			$this->Contents[$orderedProduct->getId()] = $orderedProduct;
			$this->cleanUp();
		}
	}

	/**
	 *
	 */
	private function cleanUp() {
		foreach($this->getContents() as $cartProduct){
			if ($cartProduct->getQuantity() < 1){
				$this->removeFromContents($cartProduct->getId());
			}
		}
	}

	/**
	 * @param string $id
	 */
	public function remove($id) {
		$this->removeFromContents($id);
	}

	/**
	 * @param string $id
	 */
	private function removeFromContents($id) {
		if (array_key_exists($id, $this->Contents)){
			unset($this->Contents[$id]);
		}
	}

	/**
	 * @return htmlElement_table
	 */
	public function editProducts() {
		global $currencies, $typeNames;
		$productsTable = htmlBase::newElement('table')
			->setCellPadding(3)
			->setCellSpacing(0)
			->addClass('productTable')
			->css('width', '100%');

		$buttonAdd = htmlBase::newElement('button')
			->addClass('insertProductIcon')
			->attr('data-product_entry_method', sysConfig::get('EXTENSION_ORDER_CREATOR_PRODUCT_FIND_METHOD'))
			->setText('Add Product To Order');

		$productTableHeaderColumns = array(
			array(
				'colspan' => 2,
				'text'    => sysLanguage::get('TABLE_HEADING_PRODUCTS')
			),
			array('text' => 'Barcode'),
			array('text' => sysLanguage::get('TABLE_HEADING_PRODUCTS_MODEL')),
			array('text' => sysLanguage::get('TABLE_HEADING_TAX')),
			array('text' => sysLanguage::get('TABLE_HEADING_PRICE_EXCLUDING_TAX')),
			array('text' => sysLanguage::get('TABLE_HEADING_PRICE_INCLUDING_TAX')),
			array('text' => sysLanguage::get('TABLE_HEADING_TOTAL_EXCLUDING_TAX')),
			array('text' => sysLanguage::get('TABLE_HEADING_TOTAL_INCLUDING_TAX')),
			array('text' => $buttonAdd->draw())
		);

		foreach($productTableHeaderColumns as $i => $cInfo){
			$productTableHeaderColumns[$i]['addCls'] = 'main ui-widget-header';
			if ($i > 0){
				$productTableHeaderColumns[$i]['css'] = array(
					'border-left' => 'none'
				);
			}

			if ($i > 1){
				$productTableHeaderColumns[$i]['align'] = 'right';
			}
		}

		$productsTable->addHeaderRow(array(
			'columns' => $productTableHeaderColumns
		));

		foreach($this->getContents() as $orderedProduct){
			//$productsName = '<input type="text" style="width:90%" class="ui-widget-content" name="product[' . $orderedProductId . '][name]" value="' . $orderedProduct->getName() . '">';

			$bodyColumns = array(
				array(
					'align' => 'right',
					'text'  => $orderedProduct->getQuantityEdit()
				),
				array('text' => $orderedProduct->getNameEdit()),
				array('text' => $orderedProduct->getBarcodeEdit()),
				array('text' => $orderedProduct->getModel()),
				array(
					'align' => 'right',
					'text'  => $orderedProduct->getTaxRateEdit()
				),
				array(
					'align' => 'right',
					'text'  => $orderedProduct->getPriceEdit()
				),
				array(
					'align' => 'right',
					'text'  => $orderedProduct->getPriceEdit(false, true)
				),
				array(
					'align' => 'right',
					'text'  => $orderedProduct->getPriceEdit(true, false)
				),
				array(
					'align' => 'right',
					'text'  => $orderedProduct->getPriceEdit(true, true)
				),
				array(
					'align' => 'right',
					'text'  => '<span class="ui-icon ui-icon-closethick deleteProductIcon"></span>'
				)
			);

			$sizeOf = sizeof($bodyColumns);
			foreach($bodyColumns as $idx => $colInfo){
				$bodyColumns[$idx]['addCls'] = 'ui-widget-content';
				$bodyColumns[$idx]['valign'] = 'top';
				$bodyColumns[$idx]['css'] = array(
					'border-top' => 'none'
				);

				if ($idx > 0 && $idx < $sizeOf){
					$bodyColumns[$idx]['css']['border-left'] = 'none';
				}
			}
			$bodyColumns[2]['addCls'] .= ' barcodeCol';

			$productsTable->addBodyRow(array(
				'attr'    => array(
					'data-id' => $orderedProduct->getId()
				),
				'columns' => $bodyColumns
			));
		}
		return $productsTable;
	}
}

?>