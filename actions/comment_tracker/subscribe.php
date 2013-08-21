<?php
/**
 * Subscribe/unsubscribe for chosen notification methods
 */

$entity_guid = get_input('guid');
$user_guid = get_input('user');

$user = get_user($user_guid);

// TODO Is there a case when user is NOT the logged in user?
if (!$user) {
	$user = elgg_get_logged_in_user_entity();
}

$entity = get_entity($entity_guid);

if (!$user || !$user->canEdit()) {
	register_error(elgg_echo('comment_tracker:subscribe:failed'));
	forward(REFERER);
}

if ($entity) {
	$notification_handlers = _elgg_services()->notifications->getMethodsAsDeprecatedGlobal();

	$success = true;
	foreach ($notification_handlers as $handler => $enabled) {
		$method_chosen = (boolean) get_input($handler);

		if ($method_chosen) {
			if (!comment_tracker_has_subscribed($user->guid, $handler, $entity->guid)) {
				// Add subscribtion for this method
				if (!elgg_add_subscription($user->guid, $handler, $entity->guid)) {
					$success = false;
				}
			}
		} else {
			if (comment_tracker_has_subscribed($user->guid, $handler, $entity->guid)) {
				// Remove subscription from this method
				if (!elgg_remove_subscription($user->guid, $handler, $entity->guid)) {
					$success = false;
				}
			}
		}
	}
} else {
	register_error(elgg_echo('comment_tracker:subscribe:entity:not:access'));
}

if ($success) {
	system_message(elgg_echo('comment_tracker:subscribtion:success'));
} else {
	register_error(elgg_echo('comment_tracker:subscribtion:failed'));
}

forward(REFERER);