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

// Offset is the total amount of errors so far. We skip these
// users to prevent them from possibly repeating the same error.
$offset = get_input('offset', 0);
$limit = 50;

$access_status = access_get_show_hidden_status();
access_show_hidden_entities(true);

// Prevent event or plugin hook handlers from plugins to run
$original_events = _elgg_services()->events;
$original_hooks = _elgg_services()->hooks;
_elgg_services()->events = new Elgg_EventsService();
_elgg_services()->hooks = new Elgg_PluginHooksService();
elgg_register_plugin_hook_handler('permissions_check', 'all', 'elgg_override_permissions');
elgg_register_plugin_hook_handler('container_permissions_check', 'all', 'elgg_override_permissions');

$count = 0;
$dbprefix = elgg_get_config('dbprefix');

$notification_handlers = _elgg_services()->notifications->getMethods();

$class_name = get_input('upgrade_class');
if ($class_name) {
	$upgrade = new $class_name;
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
	'numSuccess' => $success_count,
	'numErrors' => $error_count,
));