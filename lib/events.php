<?php

// annotation event handler function to manage comment notifications
function comment_tracker_notifications($event, $type, $annotation) {
	if ($type == 'annotation' && elgg_is_logged_in()) {
		if ($annotation->name == "generic_comment" || $annotation->name == "group_topic_post") {
                    
                    // subscribe the commenter to the thread if they haven't specifically unsubscribed
                    $user = get_user($annotation->owner_guid);
                    $entity = get_entity($annotation->entity_guid);
					
					comment_tracker_notify($annotation, $user);
                    
                    $autosubscribe = elgg_get_plugin_user_setting('comment_tracker_autosubscribe', $user->guid, 'comment_tracker');
                    
                    if (!comment_tracker_is_unsubscribed($user, $entity) && $autosubscribe != 'no') {
                        // don't subscribe the owner of the entity
                        if ($entity->owner_guid != $user->guid) {
                            comment_tracker_subscribe($user->guid, $entity->guid);
                        }
                    }
		}
	}
	return TRUE;
}


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