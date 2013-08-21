<?php

/**
 * Check whether the user is subscribed to the entity
 *
 * Any active notification method is considered a subscription.
 *
 * @param  ElggUser   $user
 * @param  ElggEntity $entity
 * @return boolean
 */
function comment_tracker_is_subscribed($user, $entity) {
	if (!$user || !elgg_instanceof($user, 'user')) {
		return false;
	}

	if (!$entity || !($entity instanceof ElggEntity)) {
		return false;
	}

	$db_prefix = elgg_get_config('dbprefix');

	// Use custom query to keep it as light as possible
	$result = get_data("SELECT * FROM {$db_prefix}entity_relationships
WHERE relationship LIKE 'notify%'
AND guid_one = {$user->guid}
AND guid_two = {$entity->guid}");

	$result = empty($result) ? false : true;

	$params = array(
		'user' => $user,
		'entity' => $entity
	);

	// Allow other plugins to affect the behaviour
	return elgg_trigger_plugin_hook('subscription_check', 'comment_tracker', $params, $result);
}

/**
 * returns bool - whether the user has explicitly unsubscribed
 * @param type $user
 * @param type $entity
 */
function comment_tracker_is_unsubscribed($user, $entity) {
    if (!elgg_instanceof($user, 'user')) {
		return false;
	}

	if (!elgg_instanceof($entity)) {
		return false;
	}

	$result = check_entity_relationship($user->guid, COMMENT_TRACKER_UNSUBSCRIBE_RELATIONSHIP, $entity->guid);

	$params = array('user' => $user, 'entity' => $entity);

	// allow other plugins to affect the behaviour
	return elgg_trigger_plugin_hook('unsubscription_check', 'comment_tracker', $params, $result);
}

/**
 * Subscribe user to notifications
 *
 * @param int $user_guid
 * @param int $entity_guid
 * @return boolean
 */
function comment_tracker_subscribe($user_guid, $entity_guid) {
	if (elgg_is_logged_in()) {
		if (empty($user_guid)) {
			$user_guid = elgg_get_logged_in_user_guid();
		}

		if (empty($user_guid) || empty($entity_guid)) {
			return false;
		}

		/**
		 * TODO Get rid of the COMMENT_TRACKER_RELATIONSHIP relationship.
		 * Save default methods to personal user settings and add the
		 * default "notify<method>" relationship between user and the entity
		 * for each of the selected methods. Then the core notifications system
		 * can take care of the subscriptions.
		 */
		if (!check_entity_relationship($user_guid, COMMENT_TRACKER_RELATIONSHIP, $entity_guid)) {
            // undo a subscription block
            remove_entity_relationship($user_guid, COMMENT_TRACKER_UNSUBSCRIBE_RELATIONSHIP, $entity_guid);
			return add_entity_relationship($user_guid, COMMENT_TRACKER_RELATIONSHIP, $entity_guid);
		}
	}
	return false;
}

/**
 * Unsubscribe user from notifications
 *
 * TODO Remove this function for Elgg 1.9
 */
function comment_tracker_unsubscribe($user_guid, $entity_guid) {
	if (elgg_is_logged_in()) {
		if (empty($user_guid)) {
			$user_guid = elgg_get_logged_in_user_guid();
		}

		if (empty($user_guid) || empty($entity_guid)) {
			return false;
		}

		if (check_entity_relationship($user_guid, COMMENT_TRACKER_RELATIONSHIP, $entity_guid)) {
            // add a subscription block
            add_entity_relationship($user_guid, COMMENT_TRACKER_UNSUBSCRIBE_RELATIONSHIP, $entity_guid);
			return remove_entity_relationship($user_guid, COMMENT_TRACKER_RELATIONSHIP, $entity_guid);
		}
	}
	return false;
}

/**
 *
 */
function comment_tracker_get_entity_subtypes() {
	// only allow subscriptions on objects that have comments
	// pre-populate with some common plugin objects
	// allow other plugins to add/remove subtypes
	static $subscription_subtypes;

	if (!$subscription_subtypes) {
	  $base_types = array(
		  'blog',
		  'bookmarks',
		  'event_calendar', // event calendar
		  'file',
		  'groupforumtopic',
		  'image',	// tidypics
		  'page',
		  'page_top',
		  'poll'  // poll
	  );

	  // other plugins can add allowed object subtypes in this hook
	  $subscription_subtypes = elgg_trigger_plugin_hook('subscription_types', 'comment_tracker', array(), $base_types);
    }

    return $subscription_subtypes;
}

/**
 * Get methods that user wants to use to get notification about the entity.
 *
 * This can be used when implementing a feature that allows defining content
 * specific notification preferences instead of using the default notification
 * settings for all content.
 *
 * @param  int    $user_guid   The GUID of the user
 * @param  int    $target_guid The entity to receive notifications about
 * @return array
 */
function comment_tracker_get_user_subscriptions($user_guid, $target_guid) {
	$service = _elgg_services()->notifications;

	$methods = $service->getMethods();
	if (empty($methods)) {
		return array();
	}

	// TODO Can elgg_get_subscriptions_for_container() be used instead of this?
	$db = _elgg_services()->db;
	$subs = new Elgg_Notifications_SubscriptionsService($db, $methods);
	$prefix = $subs::RELATIONSHIP_PREFIX;

	$names = array();
	foreach ($methods as $method) {
		$names[] =  sanitize_string("$prefix$method");
	}

	$methods_string = "'" . implode("','", $names) . "'";

	$db_prefix = $db->getTablePrefix();

	$query = "SELECT relationship AS method
		FROM {$db_prefix}entity_relationships
		WHERE guid_two = $target_guid
		AND guid_one = $user_guid
		AND relationship IN ($methods_string)";

	$result = $db->getData($query);

	$handlers = array();
	foreach ($result as $handler) {
		$handlers[] = $handler->method;
	}

	return $handlers;
}

/**
 * Check if user has subscribed to an entity using given method.
 *
 * @param  int    $entity_guid The entity to check against
 * @param  string $method      The notification method to check
 * @param  int    $user_guid   The user to check against
 *
 * TODO Remove once similar function has been added to core
 */
function comment_tracker_has_subscribed($user_guid, $method, $entity_guid) {
	return check_entity_relationship($user_guid, "notify{$method}", $entity_guid);
}

/**
 * Set default notification settings for content followed by the user
 *
 * TODO Might be faster to save as metadata instead?
 *
 * @param array $methods   Array of methods e.g. "email, site, sms"
 * @param int   $user_guid
 * @return boolean
 */
function comment_tracker_set_user_notification_methods (array $methods, $user_guid) {
	$methods = serialize($methods);
	return elgg_set_plugin_user_setting('notification_methods', $methods, $user_guid, 'comment_tracker');
}

/**
 * Get default notification settings for content followed by the user
 *
 * @param int $user_guid
 * @return array $methods Array of methods e.g. "email, site, sms"
 */
function comment_tracker_get_user_notification_methods ($user_guid) {
	$methods = elgg_get_plugin_user_setting('notification_methods', $user_guid, 'comment_tracker');

	if ($methods) {
		return unserialize($methods);
	}

	return array();
}