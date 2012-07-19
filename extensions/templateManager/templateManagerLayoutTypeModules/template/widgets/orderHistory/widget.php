<?php
/**
 * Sales Igniter E-Commerce System
 * Version: {ses_version}
 *
 * I.T. Web Experts
 * http://www.itwebexperts.com
 *
 * Copyright (c) {ses_copyright} I.T. Web Experts
 *
 * This script and its source are not distributable without the written consent of I.T. Web Experts
 */

class TemplateManagerWidgetOrderHistory extends TemplateManagerWidget
{

	public function __construct()
	{
		global $App;
		$this->init('orderHistory', false, __DIR__);
	}

	public function show(TemplateManagerLayoutBuilder $LayoutBuilder)
	{
		global $userAccount;
		if ($userAccount->isLoggedIn() === true){
			// retreive the last x products purchased
			$Qorders = Doctrine_Query::create()
				->select('DISTINCT op.products_id, o.orders_id, op.products_name, p.products_status')
				->from('Orders o')
				->leftJoin('o.OrdersProducts op')
				->leftJoin('op.Products p')
				->where('o.customers_id = ?', (int)$userAccount->getCustomerId())
				->groupBy('op.products_id')
				->orderBy('o.date_purchased DESC')
				->limit(sysConfig::get('MAX_DISPLAY_PRODUCTS_IN_ORDER_HISTORY_BOX'));

			EventManager::notify('OrdersListingBeforeExecute', &$Qorders);

			$Qorders = $Qorders->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
			if ($Qorders){
				$boxContent = '<table border="0" width="100%" cellspacing="0" cellpadding="1">';
				foreach($Qorders as $oInfo){
					foreach($oInfo['OrdersProducts'] as $opInfo){
						if ($opInfo['Products']['products_status'] == '1'){
							$productName = '<a href="' . itw_app_link('products_id=' . $opInfo['products_id'], 'product', 'info') . '">' . $opInfo['products_name'] . '</a>';
						}
						else {
							$productName = $opInfo['products_name'];
						}
						$boxContent .= '<tr>' .
							'<td class="infoBoxContents">' . $productName . '</td>' .
							'</tr>';
					}
				}
				$boxContent .= '</table>';

				$this->setBoxContent($boxContent);

				return $this->draw();
			}
		}
		return false;
	}
}

?>