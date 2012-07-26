<?php
class PurchaseTypeTabReservation_tab_inventory
{

	private $heading;

	private $displayOrder = 3;

	public function __construct() {
		$this->setHeading(sysLanguage::get('TAB_PURCHASE_TYPE_HEADING_INVENTORY'));
	}

	public function getDisplayOrder() {
		return $this->displayOrder;
	}

	public function setDisplayOrder($val) {
		$this->displayOrder = $val;
	}

	public function setHeading($val) {
		$this->heading = $val;
	}

	public function getHeading() {
		return $this->heading;
	}

	public function addTab(htmlWidget_tabs &$TabsObj, Product $Product, PurchaseType_reservation $PurchaseType) {
		global $tax_class_array;
		if ($PurchaseType->getConfigData('INVENTORY_ENABLED') == 'True'){
			//$PurchaseType->loadData($Product->getId());
			$purchaseTypeCode = $PurchaseType->getCode();

			$inputTable = htmlBase::newElement('table')
				->setCellPadding(2)
				->setCellSpacing(0)
				->css('width', '100%');

			$QuantityGrid = htmlBase::newGrid()
				->addClass('quantityGrid');

			$SerialsGrid = htmlBase::newGrid()
				->allowMultipleRowSelect(true)
				->addClass('serialsGrid')
				->attr(
				array(
					'data-purchase_type'  => $PurchaseType->getCode(),
					'data-default_status' => $PurchaseType->getConfigData('INVENTORY_STATUS_AVAILABLE')
				));

			$QuantityGridHeader = array(
				'columns' => array()
			);

			$QuantityGridBody = array(
				'addCls'  => 'noHover noSelect',
				'columns' => array()
			);

			$SerialsGridHeader = array(
				'columns' => array(
					array('text' => 'Serial Number'),
					array('text' => 'Status'),
					array('text' => 'Reservation Info')
				)
			);

			$availableStatuses = array();
			$inventoryColumns = $PurchaseType->getConfigData('INVENTORY_QUANTITY_STATUSES');
			$StatusSelect = htmlBase::newSelectbox()
				->addClass('serialNumberStatus');
			foreach($inventoryColumns as $id){
				$StatusSelect->addOption($id, itw_get_status_name($id));
			}

			$SerialNumber = htmlBase::newInput()
				->setType('hidden');

			$inventoryItems = $PurchaseType->getInventoryItems();
			foreach($inventoryColumns as $id){
				$StatusName = itw_get_status_name($id);
				$availableStatuses[] = array(
					'id'   => $id,
					'text' => $StatusName
				);

				$QuantityGridHeader['columns'][] = array(
					'text' => $StatusName
				);

				$total = 0;
				if (isset($inventoryItems[$id])){
					$total = $inventoryItems[$id]['total'];

					if (isset($inventoryItems[$id]['serials'])){
						foreach($inventoryItems[$id]['serials'] as $Serial){
							$SerialsGrid->addBodyRow(array(
								'columns' => array(
									array(
										'align' => 'center',
										'text'  => $SerialNumber->setName('inventory_serial[' . $purchaseTypeCode . '][number][]')->setValue($Serial)->draw() . $Serial
									),
									array(
										'align' => 'center',
										'text'  => $StatusSelect->data('previous_status', $id)->setName('inventory_serial[' . $purchaseTypeCode . '][status][]')->selectOptionByValue($id)->draw()
									),
									array(
										'align' => 'center',
										'text'  => htmlBase::newIcon()->setType('info')
									)
								)
							));

							if ($id == $PurchaseType->getConfigData('INVENTORY_STATUS_RESERVED')){
								$QReservation = Doctrine_Query::create()
									->from('PayPerRentalReservations r')
									->leftJoin('r.SaleProduct p')
									->leftJoin('p.SaleInventory i')
									->where('i.serial_number = ?', $Serial)
									->andWhere('r.rental_state = ?', 'reserved')
									->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

								$InfoRow = htmlBase::newTable()
									->setCellPadding(3)
									->setCellSpacing(0);
								foreach($QReservation as $Reservation){
									$InfoRow->addBodyRow(array(
										'columns' => array(
											array('text' => '<b>Start Date</b>'),
											array('text' => '<b>End Date</b>')
										)
									));
									$InfoRow->addBodyRow(array(
										'columns' => array(
											array('text' => $Reservation['start_date']->format(sysLanguage::getDateFormat('long'))),
											array('text' => $Reservation['end_date']->format(sysLanguage::getDateFormat('long')))
										)
									));
									$InfoRow->addBodyRow(array(
										'columns' => array(
											array('colspan' => 2, 'text' => 'View Reservation')
										)
									));
									$InfoRow->addBodyRow(array(
										'columns' => array(
											array('colspan' => 2, 'text' => '&nbsp;')
										)
									));
								}
								$SerialsGrid->addBodyRow(array(
									'addCls'  => 'gridInfoRow',
									'columns' => array(
										array(
											'colspan' => 3,
											'text' => $InfoRow
										)
									)
								));
							}
						}
					}
				}

				$QtyInput = htmlBase::newInput()
					->addClass('inventoryQuantity_' . $id)
					->setSize(8)
					->setName('inventory[' . $PurchaseType->getCode() . '][' . $id . ']')
					->setValue($total);

				$QuantityGridBody['columns'][] = array(
					'align' => 'center',
					'text'  => $QtyInput
				);
			}

			EventManager::notify('NewProductInventoryTabBottom', $Product, &$QuantityGridHeader, &$QuantityGridBody, &$PurchaseType);

			$QuantityGrid->addHeaderRow($QuantityGridHeader);
			$QuantityGrid->addBodyRow($QuantityGridBody);

			$SerialsGrid->attr('data-available_statuses', urlencode(json_encode($availableStatuses)));
			$SerialsGrid->addButtons(array(
				htmlBase::newButton()
					->addClass('genSerialButton')
					->usePreset('install')
					->setText('Auto Generate'),
				htmlBase::newButton()
					->addClass('addSerialButton')
					->usePreset('new')
					->setText('Add'),
				htmlBase::newButton()
					->addClass('deleteSerialButton')
					->usePreset('delete')
					->disable(),
			));
			$SerialsGrid->addHeaderRow($SerialsGridHeader);

			$inputTable->addBodyRow(array(
				'columns' => array(
					array('text' => $QuantityGrid)
				)
			));

			$UseSerialsCheckbox = htmlBase::newCheckbox()
				->setName('use_serials[' . $PurchaseType->getCode() . ']')
				->setLabel('Use Serial Numbers')
				->setLabelPosition('right')
				->setChecked($PurchaseType->getData('use_serials') == 1);

			$inputTable->addBodyRow(array(
				'columns' => array(
					array('text' => '<br><hr><br>' . $UseSerialsCheckbox->draw() . '<br>')
				)
			));

			$inputTable->addBodyRow(array(
				'columns' => array(
					array('text' => $SerialsGrid)
				)
			));

			$TabsObj->addTabHeader('purchaseTypeReservationSettingsTabInventory', array('text' => $this->getHeading()))
				->addTabPage('purchaseTypeReservationSettingsTabInventory', array('text' => $inputTable));
		}
	}
}
