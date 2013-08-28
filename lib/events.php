<?php

/**
 * Automatically subscibe user to a thread when they post a comment
 * 
 * The feature is ignored if user has specifically unsubscribed from
 * the specific thread.
 * 
 * @param  string     $event  'create'
 * @param  string     $type   'object'
 * @param  ElggObject $object New comment or discussion reply
 * @return boolean
 * 
 * TODO Handle group discussion replies once they get migrated to entities
 */
function comment_tracker_auto_subscribe($event, $type, $object) {
	if (elgg_instanceof($object, 'object', 'comment') && elgg_is_logged_in()) {
		comment_tracker_notify($object, elgg_get_logged_in_user_entity());

		// subscribe the commenter to the thread if they haven't specifically unsubscribed
		$user = get_user($object->owner_guid);
		$entity = get_entity($object->entity_guid);

		$autosubscribe = elgg_get_plugin_user_setting('comment_tracker_autosubscribe', $user->guid, 'comment_tracker');

		if (!comment_tracker_is_unsubscribed($user, $entity) && $autosubscribe != 'no') {
			// don't subscribe the owner of the entity
			if ($entity->owner_guid != $user->guid) {
			    comment_tracker_subscribe($user->guid, $entity->guid);
			}
		}
	}
	return TRUE;
}