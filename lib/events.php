<?php

/**
 * Automatically subscribe user to a thread when they like or comment on it. Also
 * subscribe content-creators to their own content.
 *
 * Auto-subscribe is not done if user has specifically unsubscribed from
 * the specific thread.
 *
 * By default content owners receive personal notifications based on the
 * notification settings provided by Elgg core. Comment tracker provides
 * a site-wide setting that allows it to be used as alternate subscription
 * method. If the setting is enabled, this handler takes care of subscribing
 * the owner to receive the notifications through comment tracker.
 *
 * @param string                    $event  'create'
 * @param string                    $type   'object'|'annotation'
 * @param ElggObject|ElggAnnotation $object New comment/discussion reply/like
 * @return boolean
 *
 * TODO Handle group discussion replies once they get migrated to entities
 */
function comment_tracker_auto_subscribe($event, $type, $object) {
	if (!elgg_is_logged_in()) {
		return true;
	}

	// handling this case first for readability
	if ($object instanceof ElggObject && !($object instanceof ElggComment)) {
		// new content object, try to subscribe the user to her own object

		$notify_owner = elgg_get_plugin_setting('notify_owner', 'comment_tracker');

		if ($notify_owner !== 'yes') {
			// Comment tracker isn't configured to do this
			return true;
		}

		if (!in_array($object->getSubtype(), comment_tracker_get_entity_subtypes())) {
			// Comment tracker does not currently support this content type
			return true;
		}

		$owner = $object->getOwnerEntity();
		comment_tracker_subscribe($owner->guid, $object->guid);
		return true;
	}

	// find the entity that's been liked or commented on...
	if ($object instanceof ElggComment) {
		$comment = $object;
		/* @var ElggComment $comment */
		$user = $comment->getOwnerEntity();
		$entity = $comment->getContainerEntity();

	} elseif ($object instanceof ElggAnnotation) {
		$annotation = $object;
		/* @var ElggAnnotation $annotation */
		if ($annotation->name != 'likes') {
			return true;
		}
		$user = $annotation->getOwnerEntity();
		$entity = $annotation->getEntity();

	} else {
		return true;
	}

	// if nested comment or like on a comment, walk up to containing object
	while ($entity instanceof ElggComment) {
		$entity = $entity->getContainerEntity();
	}
	if (!$entity) {
		// commented/liked an object the user can't see? unlikely but could happen.
		return true;
	}

	if (!$entity instanceof ElggObject) {
		// can't subscribe to non-objects
		return true;
	}

	if (!in_array($object->getSubtype(), comment_tracker_get_entity_subtypes())) {
		// Comment tracker does not currently support this content type
		return true;
	}

	if ($entity->owner_guid == $user->guid) {
		// don't need to subscribe the owner of the entity. Either Elgg will send these
		// notifications, or this plugin has auto-subscribed the user already.
		return true;
	}

	$autosubscribe = elgg_get_plugin_user_setting('comment_tracker_autosubscribe', $user->guid, 'comment_tracker');
	if ($autosubscribe == 'no' || comment_tracker_is_unsubscribed($user, $entity)) {
		return true;
	}

	comment_tracker_subscribe($user->guid, $entity->guid);
}
