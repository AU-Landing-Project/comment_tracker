<?php
/**
 * Notification unsuscribe page on comment tracker
 * 
 * @package ElggCommentTracker
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @copyright Copyright (c) 2007-2011 Cubet Technologies. (http://cubettechnologies.com)
 * @version  1.0
 * @author Akhilesh @ Cubet Technologies
 */

// Ensure only logged-in users can see this page
gatekeeper();
global $CONFIG;

if(!elgg_is_active_plugin('notifications') || $CONFIG->allow_commnet_notification != 'yes'){
	forward();
}

$user = elgg_get_page_owner_entity();
$entity_guid = get_input('entity_guid');
$entity = get_entity($entity_guid);

if(!empty($entity_guid) && $entity)
{
	if($user && $user->guid == elgg_get_logged_in_user_guid())
	{
		if(comment_tracker_unsubscribe($user->guid, $entity_guid))
		{
			system_message(elgg_echo('comment:unsubscribe:success'));
		}
		else
		{
			register_error(elgg_echo('comment:unsubscribe:failed'));
		}
	}
	else
	{
		register_error(elgg_echo('comment:unsubscribe:not:valid:url'));
	}
	$redirect = $entity->getUrl();
}
else
{
	register_error(elgg_echo('comment:unsubscribe:entity:not:access'));
	$redirect = '';
}
forward($redirect);