<?php

// Upgrade also possible hidden replies. This feature get run
// by an administrator so there's no need to ignore access.
$access_status = access_get_show_hidden_status();
access_show_hidden_entities(true);

// Force user to run the upgrade 1 before this one
$upgrade1 = new Upgrades_CommentTrackerUpgrade1();
if ($upgrade1->countObjects()) {
	register_error(elgg_echo('comment_tracker:upgrade:dependency', array(elgg_echo('admin:comment_tracker:upgrade1'))));
	forward('admin/upgrades');
}

$upgrade = new Upgrades_CommentTrackerUpgrade2();
$count = $upgrade->countObjects();

echo elgg_view('admin/upgrades/view', array(
	'count' => $count,
	'action' => 'action/comment_tracker/upgrade?upgrade=2',
));

access_show_hidden_entities($access_status);
