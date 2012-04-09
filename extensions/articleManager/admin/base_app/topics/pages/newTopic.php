<?php
	$Topics = Doctrine_Core::getTable('Topics');
	if (isset($_GET['tID']) && empty($_POST)){
		$Topic = $Topics->find((int)$_GET['tID']);
		$Topic->refresh(true);
		
		$sortOrder = $Topic->sort_order;
		$headingTitle = sysLanguage::get('TEXT_INFO_HEADING_EDIT_TOPIC');
	}else{
		$Topic = $Topics->getRecord();
		
		$sortOrder = 0;
		$headingTitle = sysLanguage::get('TEXT_INFO_HEADING_NEW_TOPIC');
	}
?>
<form name="new_topic" action="<?php echo itw_app_link(tep_get_all_get_params(array('app', 'appName', 'action')) . 'action=saveTopic');?>" method="post">
<div class="pageHeading"><?php
	echo $headingTitle;
?></div>
<br />

<table border="0" cellspacing="0" cellpadding="2">
<?php
    foreach (sysLanguage::getLanguages() as $lInfo) {
		$lID = $lInfo['id'];
		$val = '';
		if (isset($_GET['tID'])){
			$val = $Topic->TopicsDescription[$lID]->topics_name;
		}
?>
          <tr>
            <td class="main"><?php if ($i == 0) echo sysLanguage::get('TEXT_EDIT_TOPICS_NAME'); ?></td>
            <td class="main"><?php echo $lInfo['showName']('&nbsp;') . tep_draw_input_field('topics_name[' . $lID . ']', $val); ?></td>
          </tr>
<?php
    }
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
    foreach (sysLanguage::getLanguages() as $lInfo) {
		$lID = $lInfo['id'];
		$val = '';
		if (isset($_GET['tID'])){
			$val = $Topic->TopicsDescription[$lID]->topics_heading_title;
		}
?>
          <tr>
            <td class="main"><?php if ($i == 0) echo sysLanguage::get('TEXT_EDIT_TOPICS_HEADING_TITLE'); ?></td>
            <td class="main"><?php echo $lInfo['showName']('&nbsp;') . tep_draw_input_field('topics_heading_title[' . $lID . ']', $val); ?></td>
          </tr>
<?php
    }
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
    foreach (sysLanguage::getLanguages() as $lInfo) {
		$lID = $lInfo['id'];
		$val = '';
		if (isset($_GET['tID'])){
			$val = $Topic->TopicsDescription[$lID]->topics_description;
		}
?>
          <tr>
            <td class="main" valign="top"><?php if ($i == 0) echo sysLanguage::get('TEXT_EDIT_TOPICS_DESCRIPTION'); ?></td>
            <td><table border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td class="main" valign="top"><?php echo $lInfo['showName']('&nbsp;'); ?></td>
                <td class="main"><?php echo tep_draw_textarea_field('topics_description[' . $lID . ']', 'hard', 30, 5, stripslashes($val), 'class="makeFCK"'); ?></td>
              </tr>
            </table></td>
          </tr>
<?php
    }
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo sysLanguage::get('TEXT_EDIT_SORT_ORDER'); ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('sort_order', $sortOrder, 'size="2"'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main" align="right"><?php
        	echo htmlBase::newElement('button')->usePreset('save')->setType('submit')->draw() . 
        	'&nbsp;&nbsp;' . 
        	htmlBase::newElement('button')->usePreset('cancel')->setHref(itw_app_link('appExt=articleManager' . (isset($_GET['tID']) ? '&tID=' . $_GET['tID'] : ''), 'topics', 'default'))->draw(); ?></td>
      </form></tr>
