<?php

// annotation event handler function to manage comment notifications
function comment_tracker_notifications($event, $type, $annotation) {
	if ($type == 'annotation' && elgg_is_logged_in()) {
		if ($annotation->name == "generic_comment" || $annotation->name == "group_topic_post") {
			if (elgg_get_config('allow_comment_notification') == 'yes') {
					comment_tracker_notify($annotation, elgg_get_logged_in_user_entity());
			}
		}
	}
	return TRUE;
}