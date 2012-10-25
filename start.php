<?php
/**
 * Initialise comment tracker plugin
 * 
 * @package ElggCommentTracker
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @copyright Copyright (c) 2007-2011 Cubet Technologies. (http://cubettechnologies.com)
 * @version  1.0
 * @author Akhilesh @ Cubet Technologies
 * 
 * @ 1.8 upgrade by Matt Beckett
 */

// Initialise comment tracker plugin
function comment_tracker_init() {
	global $CONFIG;
	
	$plugin_settings = elgg_get_plugin_from_id('comment_tracker');
  elgg_set_config('allow_comment_notification', 'yes');
	elgg_set_config('email_content_type', 'text');
	
	if (isset($plugin_settings->allow_comment_notification)) {
		elgg_set_config('allow_comment_notification', $plugin_settings->allow_comment_notification);
	}
  
	if (isset($plugin_settings->email_content_type)) {
		elgg_set_config('email_content_type', $plugin_settings->email_content_type);
	}
  
	// Extend views
	elgg_extend_view('css/elgg', 'comment_tracker/css');
	
	if(elgg_is_logged_in()){
	  elgg_extend_view('page/elements/comments', "comment_tracker/manage_subscription", 400);
	  elgg_extend_view('forms/discussion/reply/save', "comment_tracker/manage_subscription", 400);
	}
	
	// Register a page handler, so we can have nice URLs
	//elgg_register_page_handler('ctracker','comment_tracker_page_handler');
	
	// Register actions
	elgg_register_action("comment_tracker/unsubscribe", elgg_get_plugins_path() . "comment_tracker/actions/unsubscribe.php", 'public');
	elgg_register_action("comment_tracker/subscribe", elgg_get_plugins_path() . "comment_tracker/actions/subscribe.php", 'public');
	elgg_register_action("comment_tracker/savesettings", elgg_get_plugins_path() . "comment_tracker/actions/settings.php");
  
  // fix typo in settings (from 1.7 version)
  run_function_once('comment_tracker_update_20121025a');
}


function comment_tracker_update_20121025a() {
  $plugin_settings = elgg_get_plugin_from_id('comment_tracker');
  $plugin_settings->allow_comment_notification = $plugin_settings->allow_commnet_notification;
}

function comment_tracker_subscribe($user_guid, $entity_guid) {
	if(elgg_is_logged_in())
	{
		if(empty($user_guid))
		{
			$user_guid = elgg_get_logged_in_user_guid();
		}
		
		if(empty($user_guid) || empty($entity_guid))
		{
			return false;
		}
		
		if(!check_entity_relationship($user_guid, 'comment_subscribe', $entity_guid))
		{
			return add_entity_relationship($user_guid, 'comment_subscribe', $entity_guid);
		}
	}
	return false;
}

function comment_tracker_unsubscribe($user_guid, $entity_guid) {
	if(elgg_is_logged_in())
	{
		if(empty($user_guid))
		{
			$user_guid = elgg_get_logged_in_user_guid();
		}
		
		if(empty($user_guid) || empty($entity_guid))
		{
			return false;
		}
		
		if(check_entity_relationship($user_guid, 'comment_subscribe', $entity_guid))
		{
			return remove_entity_relationship($user_guid, 'comment_subscribe', $entity_guid);
		}
	}
	return false;
}

function notify_comment_tracker($anotation, $ann_user, $params = NULL) {
	global $NOTIFICATION_HANDLERS, $CONFIG;
	
	$entity = '';
	
	if(is_object($anotation))
	{
		$entity = get_entity($anotation->entity_guid);
	}
	if($entity instanceof ElggObject)
	{
		$container = get_entity($entity->container_guid);
		$entity_link = $entity->getUrl();
		$group_lang = ($entity->getSubtype() == 'groupforumtopic') ? "group:" : '';
		
		if(empty($subject))
		{
			$subject = sprintf(elgg_echo("comment:notify:{$group_lang}subject"), $entity->title);
		}
		
		$options = array('relationship' => 'comment_subscribe',
						 'relationship_guid' => $anotation->entity_guid,
						 'inverse_relationship' => true,
						 'types' => 'user',
						 'limit' => 0);
		
		$users = elgg_get_entities_from_relationship($options);
		$notify_settings_link = elgg_get_site_url() . "ctracker/settings";
		
		$result = array();
		foreach ($users as $user) 
		{
			if ($user instanceof ElggUser    // make sure user is real
			    && $user->guid               // make sure user has a valid id
			    && $user->guid != $ann_user->guid)    // no point notifying the author of comment
			{
                
			    if($user->guid == $entity->owner_guid && $entity->getSubtype() != "groupforumtopic"){
			      // user is the owner of the entity being commented on
			      // and it's not a group forum topic, so core will handle notifications
			      continue;
			    }
			    
				$notify_unsubscribe_link = elgg_get_site_url() . "ctracker/unsubscribe/".$user->username."/".$entity->guid;
				
				// Results for a user are...
				$result[$user->guid] = array();
				
				foreach ($NOTIFICATION_HANDLERS as $method => $details)
				{
					if(check_entity_relationship($user->guid, 'block_comment_notify'.$method, $CONFIG->site_guid))
					{
						continue;
					}
					
					$from = $container;
					switch ($method)
					{
						case 'sms':
							$message =  sprintf(elgg_echo('comment:notify:body:header'), $user->name) . ': ' . sprintf(elgg_echo("comment:notify:body:{$group_lang}desc"), $entity->title) . $entity_link;
							if(empty($group_lang))
							{
								$from = $CONFIG->site;
							}
							break;
						case 'site':
						case 'web':
							$message =  sprintf(elgg_echo("comment:notify:{$group_lang}body:web"), $user->name, '<a href="' . $entity->getUrl() . '">' . $entity->title . '</a>', '<a href="' . $ann_user->getUrl() . '">' . $ann_user->name . '</a>',  $anotation->value, $entity_link, $notify_settings_link, $notify_unsubscribe_link);
							break;
						case 'email':
						default:
							$message =  sprintf(elgg_echo("comment:notify:{$group_lang}body:email:{$CONFIG->email_content_type}"), $user->name, '<a href="' . $entity->getUrl() . '">' . $entity->title . '</a>', '<a href="' . $ann_user->getUrl() . '">' . $ann_user->name . '</a>',  $anotation->value, $entity_link, $CONFIG->sitename, $notify_settings_link, $notify_unsubscribe_link);
							if(empty($group_lang))
							{
								$from = $CONFIG->site;
							}
							break;
					}
					
					// Extract method details from list
					$handler = $details->handler;
					
					if ((!$NOTIFICATION_HANDLERS[$method]) || (!$handler))
					{
						error_log(sprintf(elgg_echo('NotificationException:NoHandlerFound'), $method));
					}
					
					elgg_log("Sending message to {$user->guid} using $method");
	
					// Trigger handler and retrieve result.
					try 
					{
						$result[$user->guid][$method] = $handler(
							$from , 		// From entity
							$user, 			// To entity
							$subject,		// The subject
							$message, 		// Message
							$params			// Params
						);
					}
					catch (Exception $e)
					{
						error_log($e->getMessage());
					}
				}
			}
		}
		return $result;
	}
	return false;
}

// comment_tracker pagesetup
function comment_tracker_pagesetup() {
	global $CONFIG;
  
	if (elgg_get_context() == 'settings' && elgg_is_logged_in() && elgg_is_active_plugin('notifications') && $CONFIG->allow_comment_notification == 'yes')
	{	
		$item = new ElggMenuItem('ctracker_settings', elgg_echo('comment:notification:settings'), elgg_get_site_url() . "ctracker/settings");
	    elgg_register_menu_item('page', $item);
	}
}

// comment_tracker page handler
function comment_tracker_page_handler($page) {

	if (!isset($page[0])) {
		$page[0] = 'settings';
	}
	
	switch ($page[0]) {
		case "settings":
			if(include(elgg_get_plugins_path() . "comment_tracker/pages/settings.php")){
			  return TRUE;
			}
			break;
		case "unsubscribe":
			set_input('username', $page[1]);
			set_input('entity_guid', $page[2]);
			if(include(elgg_get_plugins_path() . "comment_tracker/pages/unsubscribe.php")){
			  return TRUE;
			}
			break;
	}
	return FALSE;
}

// annotation event handler function to manage comment notifications
function comment_tracker_notifications($event, $type, $anotation) {
	global $CONFIG;
	if($type == 'annotation' && elgg_is_logged_in())
	{
		if ($anotation->name == "generic_comment" || $anotation->name == "group_topic_post"){
			$entity = get_entity($anotation->entity_guid);
			
			if($anotation->owner_guid == elgg_get_logged_in_user_guid() )
			{
				comment_tracker_subscribe(elgg_get_logged_in_user_guid(), $anotation->entity_guid);
			}
			
			if($CONFIG->allow_commnet_notification == 'yes' )
			{			  
		      notify_comment_tracker($anotation, elgg_get_logged_in_user_entity());
			}
		}
	}

	return TRUE;
}

// Register event handlers
elgg_register_event_handler('init','system','comment_tracker_init');
elgg_register_event_handler('create', 'annotation','comment_tracker_notifications');
elgg_register_event_handler('pagesetup','system','comment_tracker_pagesetup');