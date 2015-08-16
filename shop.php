<?php

define('IN_PAGE', true);
define('REQUIRE_FB', true);
define('REQUIRE_TPL', true);
require_once('./common.php');


// get the current score of user
require_once('./includes/class.tournament.php');
$t = new Tournament();
$weektournament = $t->getCurrentScore();
$score = intval($weektournament['score']);

// render shop items (bird theme)
$bird_content = '';
$results = DB::query("SELECT i.*, o.enabled, o.timestamp FROM item i
					  LEFT JOIN item_owned o ON i.id = o.sid AND o.uid = %i
					  WHERE i.type = %i
					  ORDER BY i.id ASC", $auth->uid, ITEM_BIRDTHEME);

$widthAll = array();
foreach ($results as $row) {
	if( $row['timestamp'] == NULL ) {
		$itemTpl = $mustache->loadTemplate('item_bird_purchase');
		$itemArr = array(
				'id'      => $row['id'],
				'type'    => $row['type'],
				'name'    => $row['name'],
				'path'    => $row['content_path'],
				'width'   => $row['width'],
				'height'  => $row['height'],
				'is-free' => ( $row['price'] == 0 ),
				'price'   => $row['price'],
			);

	} elseif ( $row['enabled'] > 0 ) {
		$itemTpl = $mustache->loadTemplate('item_bird_enabled');
		$itemArr = array(
				'name'    => $row['name'],
				'path'    => $row['content_path'],
				'is-free' => ( $row['price'] == 0 ),
				'price'   => $row['price'],
				'width'   => $row['width'],
				'height'  => $row['height'],
			);

	} else {
		$itemTpl = $mustache->loadTemplate('item_bird_enable');
		$itemArr = array(
				'id'      => $row['id'],
				'type'    => $row['type'],
				'name'    => $row['name'],
				'path'    => $row['content_path'],
				'is-free' => ( $row['price'] == 0 ),
				'price'   => $row['price'],
				'width'   => $row['width'],
				'height'  => $row['height'],
			);
	}

	$bird_content .= $itemTpl->render($itemArr);
	$widthAll[$row['width']] = true;
}
$widthAll = array_keys($widthAll);

$css3FlyElem = '';
$css3FlyHtml = '';
foreach ($widthAll as $widthValue) {
	$itemTpl = $mustache->loadTemplate('css3_fly');
	$css3FlyElem .= $itemTpl->render(array('width3' => $widthValue*3, 'width' => $widthValue));
}
$css3FlyHtml .= $mustache->loadTemplate('css3_html')->render(array('content' => $css3FlyElem));

// render shop items (background theme)
$background_content = '';
$results = DB::query("SELECT i.*, o.enabled, o.timestamp FROM item i
					  LEFT JOIN item_owned o ON i.id = o.sid AND o.uid = %i
					  WHERE i.type = %i
					  ORDER BY i.id ASC", $auth->uid, ITEM_BACKGROUND);

foreach ($results as $row) {
	if( $row['timestamp'] == NULL ) {
		$itemTpl = $mustache->loadTemplate('item_bg_purchase');
		$itemArr = array(
				'id'          => $row['id'],
				'type'        => $row['type'],
				'name'        => $row['name'],
				'path'        => $row['content_path'],
				'is-free'     => ( $row['price'] == 0 ),
				'price'       => $row['price'],
			);

	} elseif ( $row['enabled'] > 0 ) {
		$itemTpl = $mustache->loadTemplate('item_bg_enabled');
		$itemArr = array(
				'name'    => $row['name'],
				'path'    => $row['content_path'],
				'is-free' => ( $row['price'] == 0 ),
				'price'   => $row['price'],
			);

	} else {
		$itemTpl = $mustache->loadTemplate('item_bg_enable');
		$itemArr = array(
				'id'      => $row['id'],
				'type'    => $row['type'],
				'name'    => $row['name'],
				'path'    => $row['content_path'],
				'is-free' => ( $row['price'] == 0 ),
				'price'   => $row['price'],
			);
	}

	$background_content .= $itemTpl->render($itemArr);
}

// generate the page
$tpl = $mustache->loadTemplate('shop');
echo $tpl->render(array_merge($_FB, array(
	// section toggles
	'showfb'     => true,
	'is-logined' => $auth->valid(),

	// contents
	'bird-items'       => $bird_content,
	'background-items' => $background_content,
	'score'            => $score,
	'css3-fly'		   => $css3FlyHtml,
)));

?>