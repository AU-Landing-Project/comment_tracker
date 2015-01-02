<?php

/**
 * subscribe owners to their own content always
 * whether to send the notification happens in comment_tracker_notify
 *
 * @param type $event
 * @param type $type
 * @param type $object
 */
function comment_tracker_object_creation($event, $type, $object) {

	if (!in_array($object->getSubtype(), comment_tracker_get_entity_subtypes())) {
		return;
	}

	$owner = $object->getOwnerEntity();
	if (elgg_instanceof($owner, 'user')) {
		$notify_owner = elgg_get_plugin_setting('notify_owner', 'comment_tracker');

		if ($notify_owner == 'yes') {
			comment_tracker_subscribe($owner->guid, $object->guid);
		}
	}
}