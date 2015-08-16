<?php

define('IN_PAGE', true);
define('REQUIRE_FB', true);
define('REQUIRE_TPL', true);
require_once('./common.php');


require_once('./includes/class.achievement.php');
$achievement = new Achievement();
$page_content = '';
$time = time();

// find uncompleted achievement (ignore expired)
$results = DB::query("SELECT a.*, p.value as current, ROUND(p.value/a.value*100) as percentage FROM achievement a
					  LEFT JOIN achievement_owned o ON o.aid = a.id AND o.uid = %i
					  LEFT JOIN achievement_progress p ON p.target = a.target AND p.uid = %i
					  WHERE ( a.end_time >= %i OR a.end_time = 0 ) AND
					  o.timestamp IS NULL
					  ORDER BY percentage DESC", $auth->uid, $auth->uid, $time);

// transfer to template
foreach ($results as $row) {
	if( $row['end_time'] == 0 ) {
		// normal achievement
		$itemTpl = $mustache->loadTemplate('achievement_normal');
		$time_target = '';

	} else {
		// time-limited achievement
		$itemTpl = $mustache->loadTemplate('achievement_time_limited');
		$time_target = date("H:i \o\\n j M Y", $row['end_time']);
	}

	// decide the theme
	$type = 'danger';

	$target = $row['value'];
	// specific value for TARGET_HIGHSCORE
	if( $row['target'] == TARGET_HIGHSCORE ) {
		$row['value'] = 1;
		$row['current'] = 0;
		$row['percentage'] = 0;
	}

	// generate html
	$itemHtml = $itemTpl->render(array(
		'name'              => $row['name'],
		'icon-path'         => $row['icon_path'],
		'current'           => intval($row['current']),
		'target'            => $row['value'],
		'percentage'        => $row['percentage'],
		'panel-type'        => 'panel-' . $type,
		'progress-bar-type' => 'progress-bar-' . $type,
		'requirement'       => $achievement->getDescription($row['target'], $target),
		'time'              => $time_target,
	));

	$page_content .= $itemHtml;
}

// find completed achievement
$results = DB::query("SELECT a.*, o.timestamp FROM achievement a
					  LEFT JOIN achievement_owned o ON o.aid = a.id AND o.uid = %i
					  LEFT JOIN achievement_progress p ON p.target = a.target AND p.uid = %i
					  WHERE o.timestamp IS NOT NULL
					  ORDER BY o.timestamp DESC", $auth->uid, $auth->uid);

// transfer to template
foreach ($results as $row) {
	$itemTpl = $mustache->loadTemplate('achievement_completed');

	$itemHtml = $itemTpl->render(array(
		'name'              => $row['name'],
		'icon-path'         => $row['icon_path'],
		'panel-type'        => 'panel-sucess',
		'progress-bar-type' => 'progress-bar-sucess',
		'requirement'       => $achievement->getDescription($row['target'], $row['value']),
		'time'              => date("H:i \o\\n j M Y", $row['timestamp']),
	));

	$page_content .= $itemHtml;
}

$tpl = $mustache->loadTemplate('achievement');
echo $tpl->render(array_merge($_FB, array(
	// section toggles
	'showfb'     => true,
	'is-logined' => $auth->valid(),

	// contents
	'achievement-list' => $page_content,
)));

?>