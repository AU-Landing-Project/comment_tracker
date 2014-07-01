<?php
/**
 * Initialise comment tracker plugin
 * 
 * @package ElggCommentTracker
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @copyright Copyright (c) 2007-2011 Cubet Technologies. (http://cubettechnologies.com)
 * @version 1.0
 * @author Akhilesh @ Cubet Technologies
 * 
 * @ 1.8 upgrade by Matt Beckett
 */
define('COMMENT_TRACKER_RELATIONSHIP', 'comment_subscribe');
define('COMMENT_TRACKER_UNSUBSCRIBE_RELATIONSHIP', 'comment_tracker_unsubscribed');

require_once 'lib/hooks.php';
require_once 'lib/events.php';
require_once 'lib/functions.php';

// Initialise comment tracker plugin
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
	elgg_register_action("comment_tracker/subscribe", elgg_get_plugins_path() . "comment_tracker/actions/subscribe.php");
	
	$notify_owner = elgg_get_plugin_setting('notify_owner', 'comment_tracker');
	
	if ($notify_owner == 'yes') {
		elgg_register_action("comments/add", elgg_get_plugins_path() . "comment_tracker/actions/comment.php");
		elgg_unregister_event_handler('create', 'annotation', 'discussion_reply_notifications');
	}

	// plugin hooks
	// save our settings
	elgg_register_plugin_hook_handler('action', 'notificationsettings/save', 'comment_tracker_savesettings');
	// add our subscription links
	elgg_register_plugin_hook_handler('register', 'menu:entity', 'comment_tracker_entity_menu');

	// register events
	elgg_register_event_handler('create', 'annotation','comment_tracker_notifications');
	elgg_register_event_handler('create', 'object', 'comment_tracker_object_creation');

	// set up our pages
	elgg_register_page_handler('comment_tracker', 'comment_tracker_page_handler');

	// fix typo in settings (from 1.7 version)
	run_function_once('comment_tracker_update_20121025a');
}

function comment_tracker_page_handler($page) {
	gatekeeper();
	$user = get_user_by_username($page[1]);
	
	if (!$user || !$user->canEdit()) {
		return false;
	}
	
	elgg_set_context('settings');
	
	// display subscribed items
	$content = elgg_list_entities_from_relationship(array(
		'type' => 'object',
		'subtypes' => comment_tracker_get_entity_subtypes(),
		'relationship_guid' => $user->guid,
		'relationship' => COMMENT_TRACKER_RELATIONSHIP,
		'full_view' => false,
		'order_by' => 'r.time_created DESC'
	));
	
	if (!$content) {
		$content = elgg_echo('comment:subscriptions:none');
	}
	
	elgg_push_breadcrumb(elgg_echo('settings'), elgg_get_site_url() . 'settings/user/' . $user->username);
	elgg_push_breadcrumb(elgg_echo('notifications'), elgg_get_site_url() . 'notifications/personal/' . $user->username);
	elgg_push_breadcrumb(elgg_echo('comment:subscriptions'));
	
	$title = elgg_echo('comment:subscriptions');
	
	$body = elgg_view_layout('content', array(
		'title' => $title,
		'content' => $content,
		'filter' => false
	));
	
	echo elgg_view_page($title, $body);
}

// Register event handlers
elgg_register_event_handler('init','system','comment_tracker_init');
