<?php
/**
 * Manage subscribe in comment tracker plugin
 * 
 * @package ElggCommentTracker
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @copyright Copyright (c) 2007-2011 Cubet Technologies. (http://cubettechnologies.com)
 * @version  1.0
 * @author Akhilesh @ Cubet Technologies
 */

$user = elgg_get_logged_in_user_entity();
$entity_guid = get_input('entity_guid');
$entity = get_entity($entity_guid);

if(!empty($entity_guid) && $entity)
{
	if(comment_tracker_subscribe($user->guid, $entity_guid))
	{
		system_message(elgg_echo('comment:subscribe:success'));
	}
	else
	{
		register_error(elgg_echo('comment:subscribe:failed'));
	}
	$redirect = $entity->getUrl();
}
else
{
	register_error(elgg_echo('comment:subscribe:entity:not:access'));
	$redirect = '';
}
forward($redirect);