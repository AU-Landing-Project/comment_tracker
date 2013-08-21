<?php

// Upgrade also possible hidden replies. This feature get run
// by an administrator so there's no need to ignore access.
$access_status = access_get_show_hidden_status();
access_show_hidden_entities(true);

$upgrade = new Upgrades_CommentTrackerUpgrade1();
$count = $upgrade->countObjects();

echo elgg_view('admin/upgrades/view', array(
	'count' => $count,
	'action' => 'action/comment_tracker/upgrade?upgrade=1',
));

access_show_hidden_entities($access_status);
