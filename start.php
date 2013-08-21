<?php

define(COMMENT_TRACKER_RELATIONSHIP, 'comment_subscribe');
define(COMMENT_TRACKER_UNSUBSCRIBE_RELATIONSHIP, 'comment_tracker_unsubscribed');

require_once 'lib/hooks.php';
require_once 'lib/events.php';
require_once 'lib/functions.php';

elgg_register_event_handler('init','system','comment_tracker_init');

/**
 * Initialise the plugin
 */
function comment_tracker_init() {
    if (elgg_is_logged_in()) {
        elgg_extend_view('page/elements/comments', "comment_tracker/manage_subscription", 400);
        elgg_extend_view('discussion/replies', "comment_tracker/manage_subscription", 400);
    }

	// Extend views
	elgg_extend_view('css/elgg', 'comment_tracker/css');
	elgg_extend_view('notifications/subscriptions/forminternals', 'comment_tracker/settings');
	elgg_require_js('comment_tracker/subscribe');

	// Register actions
	$actions_path = elgg_get_plugins_path() . "comment_tracker/actions/comment_tracker/";
	elgg_register_action("comment_tracker/subscribe", $actions_path . 'subscribe.php');
	elgg_register_action("comment_tracker/upgrade", $actions_path . 'upgrades/upgrade.php');

	// Register plugin hooks
	// Save personal notification settings
	elgg_register_plugin_hook_handler('action', 'notificationsettings/save', 'comment_tracker_savesettings');
	// Add link for subscribing
	elgg_register_plugin_hook_handler('register', 'menu:entity', 'comment_tracker_entity_menu');

	// Register events
	//elgg_register_event_handler('create', 'object', 'comment_tracker_auto_subscribe');
	elgg_register_event_handler('upgrade', 'system', 'comment_tracker_site_upgrade_handler');

	// Notifications
	elgg_register_notification_event('object', 'comment', array('create'));
	elgg_register_plugin_hook_handler('prepare', 'notification:create:object:comment', 'comment_tracker_prepare_notification');

	// Set up our pages
	elgg_register_page_handler('comment_tracker', 'comment_tracker_page_handler');
}

/**
 * Handle calls to "/comment_tracker"
 *
 * @param array $page
 */
function comment_tracker_page_handler($page) {
	gatekeeper();
	$user = get_user_by_username($page[1]);

	if (!$user || !$user->canEdit()) {
		return false;
	}

	// TODO What is this used for?
	elgg_set_context('settings');

	// Display the items user has subscribed to
	// TODO Remove in Elgg 1.9
	/*
	$content = elgg_list_entities_from_relationship(array(
		'relationship_guid' => $user->guid,
		'relationship' => COMMENT_TRACKER_RELATIONSHIP,
		'full_view' => false,
		'order_by' => 'r.time_created DESC'
	));
	*/

	$dbprefix = elgg_get_config('dbprefix');
	$content = elgg_list_entities(array(
		'type' => 'object',
		'joins' => array("JOIN {$dbprefix}entity_relationships er ON er.guid_two = e.guid"),
		'wheres' => array("guid_one = {$user->guid} AND relationship LIKE 'notify%'"),
		'order_by' => 'er.time_created ASC',
		'full_view' => false,
	));

	if (!$content) {
		$content = elgg_echo('comment_tracker:subscriptions:none');
	}

	elgg_push_breadcrumb(elgg_echo('settings'), "settings/user/{$user->username}");
	elgg_push_breadcrumb(elgg_echo('notifications:subscriptions:changesettings'), "notifications/personal/{$user->username}");
	elgg_push_breadcrumb(elgg_echo('comment_tracker:subscriptions'));

	$title = elgg_echo('comment_tracker:subscriptions');

	$body = elgg_view_layout('content', array(
		'title' => $title,
		'content' => $content,
		'filter' => false
	));

	echo elgg_view_page($title, $body);
}