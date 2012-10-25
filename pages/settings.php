<?php
/**
 * Notification settings for comment tracker
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

if(!elgg_is_active_plugin('notifications') || $CONFIG->allow_comment_notification != 'yes')
{
	forward();
}

set_page_owner(elgg_get_logged_in_user_guid());

// Set the context to settings
elgg_set_context('settings');

$form_body = elgg_view('comment_tracker/settings');
$body = elgg_view('input/form',array(
		'body' => $form_body,
		'method' => 'post',
		'action' => elgg_get_site_url() . 'action/comment_tracker/savesettings'
));

// Insert it into the correct canvas layout
$body = elgg_view_layout('one_sidebar', array('content' => $body));


echo elgg_view_page(elgg_echo('comment:notification:settings'), $body);
