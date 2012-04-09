<div id="tabReviews"><?php
		$Reviews = Doctrine_Manager::getInstance()
			->getCurrentConnection()
			->fetchAssoc("select count(*) as count from " . TABLE_REVIEWS . " where products_id = '" . (int)$_GET['products_id'] . "'");
         if ($Reviews[0]['count'] > 0) {
             echo sysLanguage::get('TEXT_CURRENT_REVIEWS') . ' ' . $Reviews[0]['count'];
         }
         
         echo htmlBase::newElement('button')->setText(sysLanguage::get('IMAGE_BUTTON_REVIEWS'))->setHref(itw_app_link(tep_get_all_get_params(array('appPage')), 'product', 'reviews'))->draw();
?></div>