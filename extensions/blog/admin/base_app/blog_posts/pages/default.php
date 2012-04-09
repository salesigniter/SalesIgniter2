<?php

function addGridRow($blogClass, &$tableGrid) {
	global $allGetParams;

	$postId = $blogClass['post_id'];

	$statusIcon = htmlBase::newElement('icon');
	if ($blogClass['post_status'] == '1'){
		$statusIcon->setType('circleCheck')->setTooltip('Click to disable')
			->setHref(itw_app_link($allGetParams . 'action=setflag&flag=0&pID=' . $postId));
	}
	else {
		$statusIcon->setType('circleClose')->setTooltip('Click to enable')
			->setHref(itw_app_link($allGetParams . 'action=setflag&flag=1&pID=' . $postId));
	}

	$tableGrid->addBodyRow(array(
		'rowAttr' => array(
			'data-post_id' => $postId
		),
		'columns' => array(
			array('text' => $blogClass['BlogPostsDescription'][Session::get('languages_id')]['blog_post_title']),
			array(
				'text'  => $statusIcon->draw(),
				'align' => 'center'
			)
		)
	));
}

$rows = 0;
$post_count = 0;
$lID = (int)Session::get('languages_id');

$Qposts = Doctrine_Query::create()
	->select('p.*, pd.*, p2c.*')
	->from('BlogPosts p')
	->leftJoin('p.BlogPostsDescription pd')
	->leftJoin('p.BlogPostToCategories p2c')
	->where('pd.language_id = ?', $lID)
	->orderBy('pd.blog_post_title asc, p.post_id desc');

$tableGrid = htmlBase::newElement('newGrid')
	->usePagination(true)
	->useSearching(true)
	->setQuery($Qposts);

$tableGrid->addButtons(array(
	htmlBase::newElement('button')->usePreset('new')->addClass('newButton'),
	htmlBase::newElement('button')->usePreset('edit')->addClass('editButton')->disable(),
	htmlBase::newElement('button')->usePreset('delete')->addClass('deleteButton')->disable()
));

$tableGrid->addHeaderRow(array(
	'columns' => array(
		array(
			'text' => sysLanguage::get('TABLE_HEADING_POSTS'),
			'useSearch' => true,
			'searchObj' => GridSearchObj::Like()
				->useFieldObj(htmlBase::newElement('input')->setName('search_post_name'))
				->setDatabaseColumn('pd.blog_post_title')
		),
		array('text' => sysLanguage::get('TABLE_HEADING_STATUS')),
	)
));

$posts = &$tableGrid->getResults();
if ($posts){
	$editButton = htmlBase::newElement('button')->usePreset('edit');
	$deleteButton = htmlBase::newElement('button')->usePreset('delete')->addClass('deletePostButton');

	$allGetParams = tep_get_all_get_params(array('action', 'pID', 'flag'));
	foreach($posts as $post){
		$postId = (int)$post['post_id'];
		addGridRow($post, $tableGrid);
	}
}
?>
<div class="pageHeading"><?php echo sysLanguage::get('HEADING_TITLE');?></div>
<br />
<div class="ui-widget ui-widget-content ui-corner-all" style="margin-right:5px;margin-left:5px;">
	<div style="margin:5px;">
		<?php echo $tableGrid->draw();?>
	</div>
</div>
