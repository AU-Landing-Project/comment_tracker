<?php

/**
 * Add subscribe/unsuncribe link to entity menu
 *
 * @param string         $hook   'register'
 * @param string         $type   'menu:enity'
 * @param ElggMenuItem[] $return The current menu items
 * @param array          $params Array of parameters
 * @return ElggMenuItem[]
 */
function comment_tracker_entity_menu($hook, $type, $return, $params) {
	// Only logged in users can subscribe
	if (!elgg_is_logged_in()) {
		return $return;
	}

	// Entity menu is not displayed in widgets
	if (elgg_in_context('widgets')) {
		return $return;
	}

	if (!$params['entity'] instanceof ElggObject) {
		return $return;
	}

	$entity = $params['entity'];

	$user = elgg_get_logged_in_user_entity();

	// Use comment tracker to notify also the owner if explicitly told so
	if ($user->guid == $entity->owner_guid) {
		$notify_user = elgg_get_plugin_setting('notify_owner', 'comment_tracker');

		if ($notify_user != 'yes') {
			return $return;
		}
	}

	$subscription_subtypes = comment_tracker_get_entity_subtypes();

	if (in_array($entity->getSubtype(), $subscription_subtypes)) {

		if (comment_tracker_is_subscribed($user, $entity)) {
			$text = elgg_echo('comment:unsubscribe');
		} else {
			$text = elgg_echo('comment:subscribe');
		}
		$text = "<span data-guid=\"{$entity->guid}\">$text</span>";

		$item = new ElggMenuItem('comment_tracker', $text, '#');
		$item->setTooltip(elgg_echo('comment:subscribe:tooltip'));
		$item->setLinkClass("comment-tracker-toggle");
		$item->setPriority(150);

		$return[] = $item;
	}

	return $return;
}

/**
 * Save personal notification settings
 *
 * @param string $hook  'action'
 * @param string $type  'notificationsettings/save'
 * @param bool   $return True by default
 * @param null   $params
 * @return void
 */
function comment_tracker_savesettings($hook, $type, $return, $params) {

	$guid = get_input('guid');
	$user = get_user($guid);

	if (!$user instanceof ElggUser) {
		return $return;
	}

	$site = elgg_get_site_entity();

	$notification_handlers = _elgg_services()->notifications->getMethodsAsDeprecatedGlobal();
	foreach ($notification_handlers as $method => $foo) {
		$subscriptions[$method] = get_input($method.'commentsubscriptions');

		if (!empty($subscriptions[$method])) {
			remove_entity_relationship($user->guid, 'block_comment_notify'.$method, $site->guid);
		} else {
			add_entity_relationship($user->guid, 'block_comment_notify'.$method, $site->guid);
		}
	}

	// save autosubscribe settings
	$autosubscribe = get_input('comment_tracker_autosubscribe');
	elgg_set_plugin_user_setting('comment_tracker_autosubscribe', $autosubscribe, $user->guid, 'comment_tracker');
}

/**
 * Prepare a notification message about a new comment
 *
 * @param  string $hook         Hook name
 * @param  string $type         Hook type
 * @param  object $notification The notification to prepare
 * @param  array  $params       Hook parameters
 * @return object
 */
function comment_tracker_prepare_notification($hook, $type, $notification, $params) {
	$event = $params['event'];
	/* @var Elgg_Notifications_Event $event */

	$object = $event->getObject();
	/* @var ElggObject $object */
	$entity = $object->getContainerEntity();
	$container = $entity->getContainerEntity();

	$actor = $event->getActor();
	$recipient = $params['recipient'];
	$language = $params['language'];
	$method = $params['method'];

	$type_string = "item:object:{$entity->getSubtype()}";
	$content_type = elgg_echo($type_string);

	// If no translation was found fall back to generic one
	if ($content_type == $type_string) {
		$content_type = elgg_echo('comment_tracker:item');
	}

	// Notification subject parameters
	$params = array(
		$actor->name,
		$content_type,
		$entity->getDisplayName(),
	);

	if (elgg_instanceof($container, 'group')) {
		// Use version "...in the group <group name>"
		$params[] = $container->getDisplayName();
		$subject_string = 'comment_tracker:notify:subject:group';
	} else {
		$subject_string = 'comment_tracker:notify:subject';
	}

	// "<user> commented on the <content type> <content title> [in the group <group name>]"
	$notification->subject = elgg_echo($subject_string, $params, $language);

	$notify_settings_link = elgg_get_site_url() . "notifications/personal/{$recipient->username}";

 	$notification->body = elgg_echo('comment_tracker:notify:body', array(
 		$recipient->name,
		$entity->title,
		$actor->name,
		elgg_get_excerpt($object->description),
		$entity->getURL(),
		$notify_settings_link,
	), $language);

	$params = array($actor->name, $entity->getDisplayName());
	$notification->summary = elgg_echo("river:comment:{$entity->getType()}:{$entity->getSubtype()}", $params, $language);

	return $notification;
}

/**
 * Get comment_tracker subscriptions for an entity
 *
 * The return array is of the form:
 *
 * array(
 *     <user guid> => array('email', 'sms', 'ajax'),
 * );
 *
 * @param string $hook          'get'
 * @param string $type          'subscription'
 * @param array  $subscriptions Array of subscriptions
 * @param array  $params        Array ('event' => Elgg_Notifications_Event)
 * @return array $subscriptions Array of subscriptions
 */
function comment_tracker_get_subscriptions($hook, $type, $subscriptions, $params) {
	$event = $params['event'];
	/* @var Elgg_Notifications_Event $event */

	// We want to send notification only when a comment is created, not when it's updated
	if ($event->getAction() !== 'create') {
		return $subscriptions;
	}

	// Send notifications only if the created entity is a comment
	if (!$event->getObject() instanceof ElggComment) {
		return $subscriptions;
	}

	// GUID of the entity that was commented
	$container_guid = $event->getObject()->getContainerGUID();

	// Get user GUIDs that have subscribed to this entity
	$user_guids = elgg_get_entities_from_relationship(array(
		'type' => 'user',
		'relationship_guid' => $container_guid,
		'relationship' => COMMENT_TRACKER_RELATIONSHIP,
		'inverse_relationship' => true,
		'limit' => false,
		'callback' => '_comment_tracker_extract_guid',
	));
	/* @var int[] $user_guids */

	if (!$user_guids) {
		return $subscriptions;
	}

	// Get a comma separated list of the subscribed users
	$user_guids_set = implode(',', $user_guids);

	$dbprefix = elgg_get_config('dbprefix');
	$site_guid = elgg_get_site_entity()->guid;

	// Get relationships that are used to explicitly block specific notification methods
	$blocked_relationships = get_data("
		SELECT *
		FROM {$dbprefix}entity_relationships
		WHERE relationship LIKE 'block_comment_notify%'
		AND guid_one IN ($user_guids_set)
		AND guid_two = $site_guid
	");

	// Get the methods from the relationship names
	$blocked_methods = array();
	foreach ($blocked_relationships as $row) {
		$method = str_replace('block_comment_notify', '', $row->relationship);
		$blocked_methods[$row->guid_one][] = $method;
	}

	$handlers = _elgg_services()->notifications->getMethods();

	foreach ($user_guids as $user_guid) {
		// All available notification methods on the site
		$methods = $handlers;

		// Remove the notification methods that user has explicitly blocked
		if (isset($blocked_methods[$user_guid])) {
			$methods = array_diff($methods, $blocked_methods[$user_guid]);
		}

		if ($methods) {
			$subscriptions[$user_guid] = $methods;
		}
	}

	return $subscriptions;
}

/**
 * Extract the GUID from a DB row (used as callback)
 *
 * @param stdClass $row DB row
 * @return int
 */
function _comment_tracker_extract_guid($row) {
	return (int)$row->guid;
}
