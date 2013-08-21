<?php

/**
 * Automatically subscribe user to a thread when they post a comment
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
	if (!elgg_is_logged_in()) {
		return true;
	}

	if (!$object instanceof ElggComment) {
		return true;
	}

	// Subscribe the commenter to the thread
	$user = get_user($object->owner_guid);
	$entity = get_entity($object->entity_guid);

	$autosubscribe = elgg_get_plugin_user_setting('comment_tracker_autosubscribe', $user->guid, 'comment_tracker');

	if (!comment_tracker_is_unsubscribed($user, $entity) && $autosubscribe != 'no') {
		// don't subscribe the owner of the entity
		if ($entity->owner_guid != $user->guid) {
		    comment_tracker_subscribe($user->guid, $entity->guid);
		}
	}

	return TRUE;
}

/**
 * Run upgrades
 *
 * Creates two ElggUpgrade objects that are executed individually later.
 *
 * @param string $event  'upgrade'
 * @param string $type   'system'
 * @param null   $object Upgrades do not receive any object
 */
function comment_tracker_site_upgrade_handler($event, $type, $object) {

	$upgrade1_class = new Upgrades_CommentTrackerUpgrade1();
	if ($upgrade1_class->countObjects()) {
		$upgrade1_url = '/admin/comment_tracker/upgrade1';
		$upgrade1 = new ElggUpgrade();
		if (!$upgrade1->getUpgradeFromURL($upgrade1_url)) {
			$upgrade1->setUrl($upgrade1_url);
			$upgrade1->title = 'Comment tracker settings upgrade';
			$upgrade1->description = 'This changes the way how notification method preferences are stored for comment_tracker.';
			$upgrade1->save();
		}
	}

	$upgrade2_class = new Upgrades_CommentTrackerUpgrade2();
	if ($upgrade2_class->countObjects()) {
		$upgrade2_url = '/admin/comment_tracker/upgrade2';
		$upgrade2 = new ElggUpgrade();
		if (!$upgrade2->getUpgradeFromURL($upgrade2_url)) {
			$upgrade2->setUrl($upgrade2_url);
			$upgrade2->title = 'Comment tracker subscriptions upgrade';
			$upgrade2->description = 'This changes the existing comment_tracker subscriptions to use the new Elgg 1.9 notifications system.';
			$upgrade2->save();
		}
	}
}
