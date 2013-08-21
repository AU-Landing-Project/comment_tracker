<?php
/**
 * Toggle comment_tracker subscription
 */

$subscribe = get_input('subscribe', true);
$entity_guid = get_input('guid');
$user_guid = get_input('user');

$user = get_user($user_guid);
$entity = get_entity($entity_guid);

if (!$user || !$user->canEdit()) {
	register_error(elgg_echo('comment_tracker:subscribe:failed'));
	return;
}

if (!empty($entity_guid) && $entity) {
	if ($subscribe) {
		$notification_handlers = comment_tracker_get_user_notification_methods($user_guid);

		if (empty($notification_handlers)) {
			$link = elgg_view('output/url', array(
				'href' => "notifications/personal/{$user->username}",
				'text' => elgg_echo('comment_tracker:error:no_methods:link'),
			));
			register_error(elgg_echo('comment_tracker:error:no_methods', array($link)));
		}

		$subscribed = true;
		foreach ($notification_handlers as $handler) {
			if (!comment_tracker_has_subscribed($user->guid, $handler, $entity->guid)) {
				$result = elgg_add_subscription($user->guid, $handler, $entity->guid);

				if (!$result) {
					$subscribed = false;
				}
			}
		}

		if ($subscribed) {
			system_message(elgg_echo('comment_tracker:subscribe:success'));
		} else {
			register_error(elgg_echo('comment_tracker:subscribe:failed'));
		}
	} else {
		$notification_handlers = _elgg_services()->notifications->getMethods();

		$unsubscribed = true;
		foreach ($notification_handlers as $handler => $enabled) {
			if (comment_tracker_has_subscribed($user->guid, $handler, $entity->guid)) {
				$result = elgg_remove_subscription($user->guid, $handler, $entity->guid);
				if (!$result) {
					$unsubscribed = false;
				}
			}
		}

		if ($unsubscribed) {
			system_message(elgg_echo('comment_tracker:unsubscribe:success'));
		} else {
			register_error(elgg_echo('comment_tracker:unsubscribe:failed'));
		}
	}
} else {
	register_error(elgg_echo('comment_tracker:subscribe:entity:not:access'));
}
