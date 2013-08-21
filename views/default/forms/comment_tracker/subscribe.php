<?php

$NOTIFICATION_HANDLERS = _elgg_services()->notifications->getMethodsAsDeprecatedGlobal();

$fields = '';
foreach($NOTIFICATION_HANDLERS as $method => $enabled) {
	/*
	if ($notification_settings = get_user_notification_settings($user->guid)) {
		if (isset($notification_settings->$method) && $notification_settings->$method) {
			$personalchecked[$method] = 'checked="checked"';
		} else {
			$personalchecked[$method] = '';
		}
	}
	*/

	if (comment_tracker_has_subscribed($vars['user_guid'], $method, $vars['guid'])) {
		$checked = 'checked="checked"';
	} else {
		$checked = '';
	}

	$name = elgg_echo("notification:method:{$method}");

	$fields .= <<<END
		<td>$name</td>
		<td class="{$method}togglefield">
		<a  border="0" id="{$method}personal" class="{$method}toggleOff" onclick="adjust{$method}_alt('{$method}personal');">
		<input type="checkbox" name="{$method}" id="{$method}checkbox" onclick="adjust{$method}('{$method}personal');" value="1" {$checked} /></a>
		</td><tr>
END;
}

echo <<<END
	<table id="notificationstable" cellspacing="0" cellpadding="4" width="100%">
		$fields
	</table>
END;

echo elgg_view('input/hidden', array(
	'name' => 'guid',
	'value' => $vars['guid'],
));

echo elgg_view('input/submit', array(
	'title' => elgg_echo('save'),
));