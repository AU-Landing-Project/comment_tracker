<?php

function comment_tracker_entity_menu($hook, $type, $return, $params) {
	if (!elgg_is_logged_in() || elgg_in_context('widget') || !elgg_instanceof($params['entity'], 'object')) {
		return $return;
	}
	
	$notify_user = elgg_get_plugin_setting('notify_owner', 'comment_tracker');
	
	if (($notify_user != 'yes') && (elgg_get_logged_in_user_guid() == $params['entity']->owner_guid)) {
		return $return;
	}
		
        $subscription_subtypes = comment_tracker_get_entity_subtypes();
		
		if (in_array($params['entity']->getSubtype(), $subscription_subtypes)) {
		  $text = '<span data-guid="' . $params['entity']->guid . '">';
		  if (comment_tracker_is_subscribed(elgg_get_logged_in_user_entity(), $params['entity'])) {
				$text .= elgg_echo('comment:unsubscribe');
		  } else {
			  $text .= elgg_echo('comment:subscribe');
		  }
		  $text .= '</span>';
		
		  $item = new ElggMenuItem('comment_tracker', $text, '#');
		  $item->setTooltip(elgg_echo('comment:subscribe:tooltip'));
		  $item->setLinkClass("comment-tracker-toggle");
		  $item->setPriority(150);
		
		  $return[] = $item;
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
    
	$notification_handlers = _elgg_services()->notifications->getMethodsAsDeprecatedGlobal();
	foreach($notification_handlers as $method => $foo) {
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

	$params = array($actor->name, $entity->getDisplayName());
	$notification->summary = elgg_echo("river:comment:{$entity->getType()}:{$entity->getSubtype()}", $params, $language);

	return $notification;
}
