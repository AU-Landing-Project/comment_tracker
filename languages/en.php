<?php
/**
 * comment_tracker language file
 * 
 * @package ElggCommentTracker
 */

return array(
	'comments' => "Comments",
	'comment_tracker:notification:settings' => 'Comment notifications',
	'comment_tracker:notification:settings:description' => 'Notify when comments are posted to items you have subscribed to.',
	'comment_tracker:notification:settings:how' => 'Select your method of notification',
	'comment_tracker:notification:settings:linktext' => 'View all items to which you are subscribed',
	'comment_tracker:subscriptions' => 'Subscriptions',
	'comment_tracker:subscriptions:none' => 'No current subscriptions',
	'comment_tracker:subscribe:tooltip' => 'Subscribe to receive notifications when comments are made on this content',
	'allow:comment:notification' => 'Do you want to enable notification? ',
	'email:content:type' => 'Do you want to support HTML Email? ',
	'text:email' => 'No',
	'html:email' => 'Yes',

	// Subscriptions
	'comment_tracker:subscribe' => 'Subscribe',
	'comment_tracker:unsubscribe' => 'Unsubscribe',
	'comment_tracker:entity:settings' => 'Subscription',
	'comment_tracker:popup:title' => 'Subscribtion settings',
	'comment_tracker:subscribe:long' => 'Subscribe to comment notifications',
	'comment_tracker:unsubscribe:long' => 'Unsubscribe from comment notifications',
	'comment_tracker:setting:autosubscribe' => "Auto-subscribe to content you comment on?",
	'show:subscribers' => 'Show Subscribers',
	'comment_tracker:subscribe:success' => 'You have successfully subscribed to this post or topic.',
	'comment_tracker:subscribe:failed' => "Sorry! You couldn't subscribe this post or topic.",
	'comment_tracker:subscribe:entity:not:access' => "Sorry! we couldn't find the post or topic for some reason.",
	'comment_tracker:unsubscribe:success' => 'You have successfully unsubscribed from this post or topic.',
	'comment_tracker:unsubscribe:failed' => "Sorry! You couldn't unsubscribe from this post or topic.",
	'comment_tracker:unsubscribe:not:valid:url' => 'Sorry! This is not a valid url to unsubscribe from this post or topic.',
	'comment_tracker:unsubscribe:entity:not:access' => "Sorry! we couldn't find the post or topic.",
	// New strings that may replace the old ones
	'comment_tracker:subscribtion:success' => 'Subscription settings saved successfully',
	'comment_tracker:subscribtion:failed' => 'There was an error saving your subscribtion settings',

	'comment_tracker:setting:show_button' => "Show subscribe/unsubscribe button above comments view?",
	'comment_tracker:item' => "item",
	'comment_tracker:setting:notify_owner' => "Let comment tracker handle owner notifications?",

	// Personal notifications
	'comment_tracker:notify:subject' => '%s commented on the %s "%s"',
	'comment_tracker:notify:subject:group' => '%s commented on the %s "%s" in the group %s',
	'comment_tracker:notify:body' => 'Hi %s,

There is a new comment on %s

%s wrote:

%s

You have received this notification because you have subscribed to it, or are involved in it.

You can change your notification settings here:
%s',

	// Group notifications
	'comment_tracker:notify:groupforumtopic:subject' => '%s posted to the discussion %s in the group %s',
	'comment_tracker:notify:groupforumtopic:body' => 'Hi %s,

There is a new post in the thread %s

%s wrote:

%s

You have received this notification because you have subscribed to it, or are involved in it.

You can change your notification settings here:
%s',
);