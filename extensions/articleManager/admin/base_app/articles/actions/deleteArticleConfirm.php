<?php
        if (isset($_POST['articles_id']) && isset($_POST['article_topics']) && is_array($_POST['article_topics'])) {
          $article_id = tep_db_prepare_input($_POST['articles_id']);
          $article_topics = $_POST['article_topics'];

          for ($i=0, $n=sizeof($article_topics); $i<$n; $i++) {
 			Doctrine_Manager::getInstance()
				->getCurrentConnection()
				->exec("delete from " . TABLE_ARTICLES_TO_TOPICS . " where articles_id = '" . (int)$article_id . "' and topics_id = '" . (int)$article_topics[$i] . "'");
          }

		$Check = Doctrine_Manager::getInstance()
			->getCurrentConnection()
			->fetchAssoc("select count(*) as total from " . TABLE_ARTICLES_TO_TOPICS . " where articles_id = '" . (int)$article_id . "'");

          if ($Check[0]['total'] == '0') {
 			Doctrine_Manager::getInstance()
				->getCurrentConnection()
				->exec("delete from articles where articles_id = " . (int)$article_id);
          }
		  EventManager::attachActionResponse(array(
			'success' => true
		  ), 'json');
        }else{
	        EventManager::attachActionResponse(array(
				'success' => false
			), 'json');
		}

   		//tep_redirect(tep_href_link(FILENAME_ARTICLES, 'tPath=' . $tPath));
?>