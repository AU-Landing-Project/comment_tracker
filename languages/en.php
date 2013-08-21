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
	'comment_tracker:subscribe:tooltip' => 'Receive notifications when comments are made on this content',
	'comment_tracker:subscription_settings:tooltip' => 'Manage notification settings for this content',
	'comment_tracker:notifications:enable' => 'Do you want to enable notification? ',

	// Subscriptions
	'comment_tracker:subscribe' => 'Subscribe',
	'comment_tracker:unsubscribe' => 'Unsubscribe',
	'comment_tracker:entity:settings' => 'Subscription',
	'comment_tracker:popup:title' => 'subscription settings',
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
	'comment_tracker:subscription:success' => 'Subscription settings saved successfully',
	'comment_tracker:subscription:failed' => 'There was an error saving your subscription settings',
	'comment_tracker:error:no_methods' => "You haven't defined notification settings for comment notifications. You can add them %s at the end of the page.",
	'comment_tracker:error:no_methods:link' => 'here',

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

	// Admin panel
	'admin:comment_tracker' => 'Comment tracker',
	'admin:comment_tracker:upgrade' => 'Upgrades',
	'comment_tracker:upgrade:dependency' => 'You need to run the %s before this one.',
	'admin:comment_tracker:upgrade1' => 'Settings upgrade',
	'admin:comment_tracker:upgrade2' => 'Subscriptions upgrade',
	'comment_tracker:upgrade:usersettings_failure' => 'Failed to upgrade notification settings for user %s (GUID: %s)',
);
