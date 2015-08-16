<?php

define('IN_PAGE', true);
define('REQUIRE_TPL', true);
require_once('./common.php');


// get all faq and user's voting record
$results = DB::query("SELECT f.*, v.vote FROM faq f LEFT JOIN faq_vote v ON v.uid = %i AND f.id = v.qid ORDER BY f.id ASC", $auth->uid);

$faq_number = 1;
$faq_content = '';

// process items into template
foreach ($results as $row) {
	$itemTpl = $mustache->loadTemplate('faq_panel');
	$itemHtml = $itemTpl->render(array(
			'id'           => $row['id'],
			'question'     => 'Q' . $faq_number . '. ' . $row['question'],
			'answer'       => $row['answer'],
			'is-upvoted'   => (intval($row['vote']) == UPVOTE),
			'is-downvoted' => (intval($row['vote']) == DOWNVOTE),
			'is-logined' => $auth->valid(),
		));

	$faq_content .= $itemHtml;
	$faq_number++;
}

// output everything to page
$tpl = $mustache->loadTemplate('faq');
echo $tpl->render(array_merge($_FB, array(
	// section toggles
	'showfb'     => true,
	'is-logined' => $auth->valid(),

	// contents
	'faq_content' => $faq_content,
)));

?>