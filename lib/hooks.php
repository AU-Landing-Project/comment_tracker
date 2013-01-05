<?php

function comment_tracker_entity_menu($hook, $type, $return, $params) {
	if (elgg_is_logged_in()
			&& (elgg_get_logged_in_user_guid() != $params['entity']->owner_guid)
			&& !elgg_in_context('widget')
			&& elgg_instanceof($params['entity'], 'object')
		) {
		
		// only allow subscriptions on objects that have comments
		// pre-populate with some common plugin objects
		// allow other plugins to add/remove subtypes
		static $subscription_subtypes;
		
		if (!$subscription_subtypes) {
		  $base_types = array(
			  'blog',
			  'bookmarks',
			  'event_calendar', // event calendar
			  'file',
			  'groupforumtopic',
			  'image',	// tidypics
			  'page',
			  'page_top',
			  'poll'  // poll
		  );
		  
		  // other plugins can add allowed object subtypes in this hook
		  $subscription_subtypes = elgg_trigger_plugin_hook('subscription_types', 'comment_tracker', array(), $base_types);
		}
		
		if (in_array($params['entity']->getSubtype(), $subscription_subtypes)) {
		  $text = '<span data-guid="' . $params['entity']->guid . '">';
		  if (comment_tracker_is_subscribed(elgg_get_logged_in_user_entity(), $params['entity'])) {
				$text .= elgg_echo('comment:unsubscribe');
		  } else {
			  $text .= elgg_echo('comment:subscribe');
		  }
		  $text .= '</span>';
		
		  $item = new ElggMenuItem('comment_tracker', $text, '#');
		  $item->setTooltip(elgg_echo('comment:subscribe:tooltip'));
		  $item->setLinkClass("comment-tracker-toggle");
		  $item->setPriority(150);
		
		  $return[] = $item;
		}
	}
	
	return $return;
}

/*
 * called on the notification settings save action
 * save our settings
 */
function comment_tracker_savesettings($hook, $type, $return, $params) {
	global $NOTIFICATION_HANDLERS, $CONFIG;
	foreach($NOTIFICATION_HANDLERS as $method => $foo) {
		$subscriptions[$method] = get_input($method.'commentsubscriptions');
		
		if (!empty($subscriptions[$method])) {
			remove_entity_relationship(elgg_get_logged_in_user_guid(), 'block_comment_notify'.$method, $CONFIG->site_guid);
		} else {
			add_entity_relationship(elgg_get_logged_in_user_guid(), 'block_comment_notify'.$method, $CONFIG->site_guid);
		}
	}
}