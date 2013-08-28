<?php

$notification_handlers = _elgg_services()->notifications->getMethods();

$fields = '';
foreach($notification_handlers as $method) {
	if (comment_tracker_has_subscribed($vars['user_guid'], $method, $vars['guid'])) {
		$checked = 'checked="checked"';
	} else {
		$checked = '';
	}

	$name = elgg_echo("notification:method:{$method}");

	$fields .= <<<END
		<tr>
			<td>$name</td>
			<td class="{$method}togglefield">
			<a class="{$method}toggleOff">
				<input type="checkbox" name="{$method}" id="{$method}checkbox" value="1" {$checked} />
			</a>
			</td>
		</tr>
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