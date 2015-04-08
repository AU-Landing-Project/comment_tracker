<?php

/**
 * Subscribe owners to their own content
 *
 * By default content owners receive personal notifications based on the
 * notification settings provided by Elgg core. Comment tracker provides
 * a site-wide setting that allows it to be used as alternate subscription
 * method. If the setting is enabled, this handler takes care of subscribing
 * the owner to receive the notifications through comment tracker.
 *
 * @param string     $event  'create'
 * @param string     $type   'object'
 * @param ElggObject $object The object being created
 */
function comment_tracker_subscribe_owner_automatically($event, $type, $object) {
	$notify_owner = elgg_get_plugin_setting('notify_owner', 'comment_tracker');

	if ($notify_owner !== 'yes') {
		// The setting is not enabled
		return true;
	}

	if (!in_array($object->getSubtype(), comment_tracker_get_entity_subtypes())) {
		// Comment tracker does not currently support this content type
		return true;
	}

	$owner = $object->getOwnerEntity();

	comment_tracker_subscribe($owner->guid, $object->guid);
}

///**
// * Listen for new likes and fire a custom event to notify the top-level content that
// * a notification needs to be sent (based on comment tracker subscriptions).
// *
// * Without this, users subscribed to items via this plugin will not get notifications
// * on likes!
// *
// * @param string         $event      "create"
// * @param string         $type       "annotation"
// * @param ElggAnnotation $annotation An Elgg annotation
// */
//function comment_tracker_like_created($event, $type, ElggAnnotation $annotation) {
//	if ($annotation->name !== 'likes') {
//		return;
//	}
//	$object = $annotation->getEntity();
//	while ($object instanceof ElggComment) {
//		$object = $object->getContainerEntity();
//	}
//	if (!($object instanceof ElggObject)) {
//		return;
//	}
//	elgg_trigger_event('commented_on', 'object', $object);
//}
