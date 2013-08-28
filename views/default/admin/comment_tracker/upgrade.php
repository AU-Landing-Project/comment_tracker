<?php

elgg_register_js('elgg.commentTrackerUpgrade', 'mod/comment_tracker/views/default/js/comment_tracker/upgrade.js');
elgg_load_js('elgg.commentTrackerUpgrade');

$upgrades_path = elgg_get_plugins_path() . 'comment_tracker/classes/upgrades/';
$upgrades = scandir($upgrades_path);

foreach ($upgrades as $upgrade_file) {
	// Make sure this is a file and not a directory
	if (is_dir($upgrades_path . "$upgrade_file")) {
		continue;
	}

	//elgg_dump($upgrades_path . "$upgrade_file");

	$upgrade_class = 'upgrades_' . str_replace(".php", '', $upgrade_file);

	$upgrade = new $upgrade_class;
	$objects = $upgrade->countObjects();

echo <<<HTML
	<div>$objects</div>
	<div class="elgg-progressbar"></div>
HTML;
}