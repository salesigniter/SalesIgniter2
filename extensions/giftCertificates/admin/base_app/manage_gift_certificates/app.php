<?php
    $purchaseTypeNames = $typeNames;
    $purchaseTypeNames['global'] = 'All Purchase Types';
    $tax_class_array = array(array('id' => '0', 'text' => sysLanguage::get('TEXT_NONE')));
    $QtaxClass = Doctrine_Query::create()
            ->select('tax_class_id, tax_class_title')
            ->from('TaxClass')
            ->orderBy('tax_class_title')
            ->execute()->toArray();
    foreach($QtaxClass as $taxClass){
        $tax_class_array[] = array(
            'id'   => $taxClass['tax_class_id'],
            'text' => $taxClass['tax_class_title']
        );
    }

    $appContent = $App->getAppContentFile();
?>