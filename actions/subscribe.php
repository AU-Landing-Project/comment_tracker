<?php
/**
 * Manage subscribe in comment tracker plugin
 * 
 * @package ElggCommentTracker
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @copyright Copyright (c) 2007-2011 Cubet Technologies. (http://cubettechnologies.com)
 * @version 1.0
 * @author Akhilesh @ Cubet Technologies
 * 
 * updated/improved by Matt Beckett
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
	$notification_handlers = _elgg_services()->notifications->getMethodsAsDeprecatedGlobal();

	if ($subscribe) {
		foreach ($notification_handlers as $handler => $enabled) {
			
			$method_enabled = get_input($handler);
			register_error("Method: $handler, Enabled: $method_enabled");

			//if (!check_entity_relationship($user->guid, "block_comment_notify{$method}", elgg_get_site_entity()->guid)) {

			if (comment_tracker_has_subscribed($entity->guid, $handler, $user->guid)) {
				register_error(elgg_echo('comment_tracker:subscribe:failed'));
			} else {
				if (elgg_add_subscription($user->guid, $handler, $entity->guid)) {
					system_message(elgg_echo('comment_tracker:subscribe:success'));
				} else {
					register_error(elgg_echo('comment_tracker:subscribe:failed'));
				}
			}
		}
	} else {
		//if (!check_entity_relationship($user->guid, "notify{$method}", $entity->guid)) {
		//	register_error("Already unsubscribed from method {$method}");
		//} else {
			$result = true;
			foreach ($notification_handlers as $handler => $enabled) {
				//if (!check_entity_relationship($user->guid, "block_comment_notify{$method}", elgg_get_site_entity()->guid)) {
					$result = elgg_remove_subscription($user->guid, $handler, $entity->guid);
				//}

				if ($result) {
					system_message(elgg_echo('comment_tracker:unsubscribe:success'));
				} else {
					register_error(elgg_echo('comment_tracker:unsubscribe:failed'));
				}
			}
		//}
	}

	/*
	if ($subscribe) {
		if (comment_tracker_subscribe($user->guid, $entity_guid)) {
			system_message(elgg_echo('comment_tracker:subscribe:success'));
		} else {
			register_error(elgg_echo('comment_tracker:subscribe:failed'));
		}
	} else {
		if (comment_tracker_unsubscribe($user->guid, $entity_guid)) {
			system_message(elgg_echo('comment_tracker:unsubscribe:success'));
		} else {
			register_error(elgg_echo('comment_tracker:unsubscribe:failed'));
		}
	}
	*/
} else {
	register_error(elgg_echo('comment_tracker:subscribe:entity:not:access'));
}