<?php
/**
 * Notification settings for comment tracker save
 * 
 * @package ElggCommentTracker
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @copyright Copyright (c) 2007-2011 Cubet Technologies. (http://cubettechnologies.com)
 * @version  1.0
 * @author Akhilesh @ Cubet Technologies
 */

// Load important global vars
global $NOTIFICATION_HANDLERS, $CONFIG;
foreach($NOTIFICATION_HANDLERS as $method => $foo) {
	$subscriptions[$method] = get_input($method.'subscriptions');

	if(!empty($subscriptions[$method]))
	{
		remove_entity_relationship(elgg_get_logged_in_user_guid(), 'block_comment_notify'.$method, $CONFIG->site_guid);
	}
	else
	{
		add_entity_relationship(elgg_get_logged_in_user_guid(), 'block_comment_notify'.$method, $CONFIG->site_guid);
	}
}

system_message(elgg_echo('comment:notification:settings:success'));

forward(REFERER);
