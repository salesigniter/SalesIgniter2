
<div class="pageHeading"><?php
	echo sysLanguage::get('HEADING_TITLE');
?></div>
<br />
  <table border="0" width="100%" cellspacing="0" cellpadding="2">
   <tr>
    <td>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
     <tr>
      <td width="50%" class="main" valign="top"><fieldset>
       <legend><?php echo sysLanguage::get('LEGEND_FROM_DATE');?></legend>
       <div type="text" id="DP_startDate"></div><br>
	   <input type="text" name="start_date" id="start_date" value="<?php echo (isset($_GET['start_date']))?$_GET['start_date']:date('Y-m-d');?>">
      </fieldset></td>
      <td width="50%" class="main" valign="top"><fieldset>
       <legend><?php echo sysLanguage::get('LEGEND_TO_DATE');?></legend>
       <div type="text" id="DP_endDate"></div><br>
	      <input type="text" name="end_date" id="end_date" value="<?php echo (isset($_GET['end_date']))?$_GET['end_date']:date('Y-m-d');?>">
       </fieldset></td>
     </tr>
     <tr>
      <td colspan="2" align="right"><?php
      echo htmlBase::newElement('button')
      ->setType('submit')
      ->setName('filter_apply')
      ->setId('filter_apply')
      ->usePreset('continue')
      ->setText('Apply Filter')
      ->draw();
      ?></td>
     </tr>
    </table>
  </td>
   </tr>
   <tr>
    <td>
	    <table border="0" width="100%" cellspacing="0" cellpadding="0">
     <tr>
      <td></td>
     </tr>
     <tr>
      <td class="main"><?php echo sysLanguage::get('TEXT_INFO_CHECK_RETURNS');?></td>
     </tr>
     <tr>
      <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="3" id="reservationsTable">
	      <thead>
       <tr class="dataTableHeadingRow">
        <td valign="top" class="dataTableHeadingContent" align="center"><?php echo sysLanguage::get('TABLE_HEADING_RETURN');?></td>
        <td valign="top" class="dataTableHeadingContent"><?php echo sysLanguage::get('TABLE_HEADING_CUSTOMERS_NAME');?></td>
        <td valign="top" class="dataTableHeadingContent"><?php echo sysLanguage::get('TABLE_HEADING_PRODUCTS_NAME');?></td>
        <td valign="top" class="dataTableHeadingContent"><?php echo sysLanguage::get('TABLE_HEADING_INV_NUM');?></td>
        <?php
        if ($centersEnabled){
        	if ($centersStockMethod == 'Store'){
        		echo '<td valign="top" class="dataTableHeadingContent">Store</td>';
        	}else{
        		echo '<td valign="top" class="dataTableHeadingContent">Inventory Center</td>';
        	}
        }
        ?>
        <td valign="top" class="dataTableHeadingContent"><?php echo 'Dates';?></td>
        <td valign="top" class="dataTableHeadingContent"><?php echo sysLanguage::get('TABLE_HEADING_DAYS_LATE');?></td>
        <!--<td valign="top" class="dataTableHeadingContent"><?php echo sysLanguage::get('TABLE_HEADING_ADD_LATE_FEE');?></td>-->
        <td valign="top" class="dataTableHeadingContent"><?php echo sysLanguage::get('TABLE_HEADING_COMMENTS');?></td>
	       <?php
	  if(sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_MAINTENANCE') == 'False'){
?>
        <td valign="top" class="dataTableHeadingContent" align="center"><?php echo sysLanguage::get('TABLE_HEADING_ITEM_DMG');?></td>
        <td valign="top" class="dataTableHeadingContent" align="center"><?php echo sysLanguage::get('TABLE_HEADING_ITEM_LOST');?></td>
		  <?php
	  }
?>
       </tr>
	      </thead>
	      <tbody>

	      </tbody>

      </table></td>
     </tr>
    </table></td>
   </tr>
	  <tr>
		  <td align="right" height="35" valign="middle"><?php
      echo htmlBase::newElement('button')
			  ->setName('return')
			  ->addClass('returnButton')
			  ->usePreset('save')
			  ->setText(sysLanguage::get('TEXT_BUTTON_RETURN_RENTALS'))
			  ->draw();
			  ?></td>
	  </tr>
  </table>
<div id="ajaxLoader" title="Ajax Operation">Performing An Ajax Operation<br>Please Wait....</div>