<?php
/**
 * Upgrade old user settings and subscriptions
 *
 * Run for 2 seconds per request as set by $batch_run_time_in_secs. This includes
 * the engine loading time.
 */

// from engine/start.php
global $START_MICROTIME;
$batch_run_time_in_secs = 2;

$upgrade_number = get_input('upgrade');

// If upgrade has run correctly, mark it done
if (get_input('upgrade_completed')) {
	// set the upgrade as completed
	$factory = new ElggUpgrade();
	$upgrade = $factory->getUpgradeFromURL("/admin/comment_tracker/upgrade{$upgrade_number}");
	if ($upgrade instanceof ElggUpgrade) {
		$upgrade->setCompleted();
	}

	return true;
}

// Offset is the total amount of errors so far. We skip these
// users to prevent them from possibly repeating the same error.
$offset = get_input('offset', 0);

$access_status = access_get_show_hidden_status();
access_show_hidden_entities(true);

// Prevent event or plugin hook handlers from plugins to run
$original_events = _elgg_services()->events;
$original_hooks = _elgg_services()->hooks;
_elgg_services()->events = new Elgg_EventsService();
_elgg_services()->hooks = new Elgg_PluginHooksService();
elgg_register_plugin_hook_handler('permissions_check', 'all', 'elgg_override_permissions');
elgg_register_plugin_hook_handler('container_permissions_check', 'all', 'elgg_override_permissions');

$upgrade_class_file = elgg_get_plugins_path() . 'comment_tracker/classes/Upgrades/' . $class_name;

if (file_exists($upgrade_class_file)) {
	$class_name = "Upgrades_CommentTrackerUpgrade{$upgrade_number}";
	$upgrade = new $class_name;
} else {
	register_error(elgg_echo('error'));
}

do {
	if ($upgrade->isBatchUpgrade()) {
		$upgrade->setOffset($offset);
	}
	$upgrade->run();
	$offset = $upgrade->getOffset();

} while ((microtime(true) - $START_MICROTIME) < $batch_run_time_in_secs);

access_show_hidden_entities($access_status);

// replace events and hooks
_elgg_services()->events = $original_events;
_elgg_services()->hooks = $original_hooks;

// Give some feedback for the UI
echo json_encode(array(
	'numSuccess' => $upgrade->getSuccessCount(),
	'numErrors' => $upgrade->getOffset(),
));
