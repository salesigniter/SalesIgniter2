<?php
        if (isset($_POST['topics_id'])) {
          $topics_id = tep_db_prepare_input($_POST['topics_id']);

          $topics = tep_get_topic_tree($topics_id, '', '0', '', true);
          $articles = array();
          $articles_delete = array();

          for ($i=0, $n=sizeof($topics); $i<$n; $i++) {
			$QarticleIds = Doctrine_Manager::getInstance()
				->getCurrentConnection()
				->fetchAssoc("select articles_id from " . TABLE_ARTICLES_TO_TOPICS . " where topics_id = '" . (int)$topics[$i]['id'] . "'");

            foreach ($QarticleIds as $article_ids) {
              $articles[$article_ids['articles_id']]['topics'][] = $topics[$i]['id'];
            }
          }

          reset($articles);
          while (list($key, $value) = each($articles)) {
            $topic_ids = '';

            for ($i=0, $n=sizeof($value['topics']); $i<$n; $i++) {
              $topic_ids .= "'" . (int)$value['topics'][$i] . "', ";
            }
            $topic_ids = substr($topic_ids, 0, -2);

 			$Check = Doctrine_Manager::getInstance()
				->getCurrentConnection()
				->fetchAssoc("select count(*) as total from " . TABLE_ARTICLES_TO_TOPICS . " where articles_id = '" . (int)$key . "' and topics_id not in (" . $topic_ids . ")");
            if ($Check[0]['total'] < '1') {
              $articles_delete[$key] = $key;
            }
          }

// removing topics can be a lengthy process
          tep_set_time_limit(0);
          for ($i=0, $n=sizeof($topics); $i<$n; $i++) {
            tep_remove_topic($topics[$i]['id']);
          }

          reset($articles_delete);
          while (list($key) = each($articles_delete)) {
            tep_remove_article($key);
          }
        }

        if (USE_CACHE == 'true') {
          tep_reset_cache_block('topics');
        }

        tep_redirect(tep_href_link(FILENAME_ARTICLES, 'tPath=' . $tPath));
?>