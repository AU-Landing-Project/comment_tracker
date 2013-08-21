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

	if (($notify_user != 'yes') && (elgg_get_logged_in_user_guid() == $entity->owner_guid)) {
		return $return;
	}

    $subscription_subtypes = comment_tracker_get_entity_subtypes();

	if (in_array($entity->getSubtype(), $subscription_subtypes)) {
		if (comment_tracker_is_subscribed(elgg_get_logged_in_user_entity(), $params['entity'])) {
			$subcribtion_text = elgg_echo('comment_tracker:unsubscribe');
		} else {
			$subcribtion_text = elgg_echo('comment_tracker:subscribe');
		}
		$text = "<span data-guid=\"{$entity->guid}\">$subcribtion_text</span>";

		$title = elgg_echo('comment_tracker:popup:title');
		$body = elgg_view('comment_tracker/popup', array('entity' => $entity));
		$popup = elgg_view_module('popup', $title, $body, array(
			'class' => 'comment-tracker-popup hidden',
			'id' => "comment-tracker-popup-{$entity->guid}",
		));

		/*
		$item = new ElggMenuItem('comment_tracker', $text, "#comment-tracker-popup-{$entity->guid}");
		$item->setTooltip(elgg_echo('comment_tracker:subscribe:tooltip'));
		$item->setLinkClass("comment-tracker-toggle");
		$item->setData('rel', 'popup');
		$item->setPriority(150);
		$return[] = $item;
		*/

		$return[] = ElggMenuItem::factory(array(
			'name' => 'comment_tracker',
			'text' => $text,
			'href' => "#comment-tracker-popup-{$entity->guid}",
			'rel' => 'popup',
			'priority' => 150,
			'title' => elgg_echo('comment_tracker:subscribe:tooltip'),
			'link_class' => "comment-tracker-toggle",
		));

		// 
		$return[] = ElggMenuItem::factory(array(
			'name' => 'comment_tracker_popup',
			'text' => $popup,
			'href' => "#comment-tracker-popup-{$entity->guid}",
			'rel' => 'popup',
			'priority' => 150,
			'title' => elgg_echo('comment_tracker:subscribe:tooltip'),
			'link_class' => "comment-tracker-toggle",
		));
	}

	return $return;
}

/*
 * called on the notification settings save action
 * save our settings
 */
function comment_tracker_savesettings($hook, $type, $return, $params) {
    
    $guid = get_input('guid');
    $user = get_user($guid);
    
    if (!elgg_instanceof($user, 'user')) {
        return $return;
    }
    
	global $NOTIFICATION_HANDLERS;
	foreach($NOTIFICATION_HANDLERS as $method => $foo) {
		$subscriptions[$method] = get_input($method.'commentsubscriptions');
		
		if (!empty($subscriptions[$method])) {
			remove_entity_relationship($user->guid, 'block_comment_notify'.$method, elgg_get_site_entity()->guid);
		} else {
			add_entity_relationship($user->guid, 'block_comment_notify'.$method, elgg_get_site_entity()->guid);
		}
	}
    
    // save autosubscribe settings
    $autosubscribe = get_input('comment_tracker_autosubscribe');
    elgg_set_plugin_user_setting('comment_tracker_autosubscribe', $autosubscribe, $user->guid, 'comment_tracker');
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

	// These are for debugging. They're displayed when running /cron/minute
	echo "<p>{$notification->subject}</p>";
	echo "<p>{$notification->body}</p>";
	echo "<p>{$notification->summary}</p>";
	echo "<hr />";

	return $notification;
}