<?php

/**
 * Register entity menu item
 *
 * @param  string         $hook   'register'
 * @param  string         $type   'menu:entity'
 * @param  ElggMenuItem[] $return Array of ElggMenuItem objects
 * @param  array          $params Menu view parameters
 * @return ElggMenuItem[] $return Array of ElggMenuItem objects
 */
function comment_tracker_entity_menu($hook, $type, $return, $params) {
	$entity = $params['entity'];

	if (!elgg_is_logged_in() || elgg_in_context('widget') || !elgg_instanceof($entity, 'object')) {
		return $return;
	}

	$notify_user = elgg_get_plugin_setting('notify_owner', 'comment_tracker');

	$user = elgg_get_logged_in_user_entity();

	if (($notify_user != 'yes') && ($user->guid == $entity->owner_guid)) {
		return $return;
	}

    $subscription_subtypes = comment_tracker_get_entity_subtypes();

	if (in_array($entity->getSubtype(), $subscription_subtypes)) {
		$is_subscribed = comment_tracker_is_subscribed($user, $entity);

		if ($is_subscribed) {
			$subcribtion_text = elgg_echo('comment_tracker:unsubscribe');
		} else {
			$subcribtion_text = elgg_echo('comment_tracker:subscribe');
		}
		$text = "<span data-guid=\"{$entity->guid}\">$subcribtion_text</span>";

		$data = array(
			'user' => $user->getGUID(),
			'guid' => $entity->getGUID(),
			'subscribe' => !$is_subscribed,
		);
		$query = http_build_query($data);

		$return[] = ElggMenuItem::factory(array(
			'name' => 'comment_tracker',
			'text' => $text,
			'href' => "action/comment_tracker/subscribe?{$query}",
			'priority' => 150,
			'title' => elgg_echo('comment_tracker:subscribe:tooltip'),
			'link_class' => "comment-tracker-toggle",
			'is_action' => true,
		));
	}

	return $return;
}

/**
 * Save plugin usersettings
 */
function comment_tracker_savesettings($hook, $type, $return, $params) {
	$user_guid = get_input('guid');
	$user = get_user($user_guid);

	if (!elgg_instanceof($user, 'user')) {
		return $return;
	}

	$notification_handlers = _elgg_services()->notifications->getMethods();

	$enabled_methods = array();
	foreach ($notification_handlers as $method) {
		$enabled = get_input("{$method}commentsubscriptions");

		if ($enabled === 'on') {
			$enabled_methods[] = $method;
		}
	}

	comment_tracker_set_user_notification_methods($enabled_methods, $user_guid);

	/*
	// TODO save autosubscribe settings
	$autosubscribe = get_input('comment_tracker_autosubscribe');
	elgg_set_plugin_user_setting('comment_tracker_autosubscribe', $autosubscribe, $user->guid, 'comment_tracker');
	*/
}

/**
 * Prepare a notification message about a new comment
 *
 * @param  string                          $hook         Hook name
 * @param  string                          $type         Hook type
 * @param  Elgg_Notifications_Notification $notification The notification to prepare
 * @param  array                           $params       Hook parameters
 * @return Elgg_Notifications_Notification
 */
function comment_tracker_prepare_notification($hook, $type, $notification, $params) {
	$object = $params['event']->getObject();
	$entity = $object->getContainerEntity();
	$container = $entity->getContainerEntity();

	$actor = $params['event']->getActor();
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
	$notification->summary = "river:comment:{$entity->getType()}:{$entity->getSubtype()}";

	return $notification;
}