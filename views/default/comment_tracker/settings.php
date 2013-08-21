<?php
/**
 * Notification settings for comment tracker view
 *
 * @package ElggCommentTracker
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @copyright Copyright (c) 2007-2011 Cubet Technologies. (http://cubettechnologies.com)
 * @author Akhilesh @ Cubet Technologies
 */

$user = elgg_get_page_owner_entity();
$view_all_link = elgg_view('output/url', array(
	'text' => elgg_echo('comment_tracker:notification:settings:linktext'),
	'href' => 'comment_tracker/subscribed/' . $user->username,
	'is_trusted' => true
));
$body = elgg_echo('comment_tracker:notification:settings:description');
$body .= "<br>" . $view_all_link;

$body .= '<br><br>';

$body .= elgg_echo('comment_tracker:setting:autosubscribe') . '&nbsp;';

$value = elgg_get_plugin_user_setting('comment_tracker_autosubscribe', $user->guid, 'comment_tracker');
$body .= elgg_view('input/dropdown', array(
    'name' => 'comment_tracker_autosubscribe',
    'value' => $value ? $value : 'yes',
    'options_values' => array(
        'yes' => elgg_echo('option:yes'),
        'no' => elgg_echo('option:no')
    )
));

echo elgg_view_module('info', elgg_echo('comment_tracker:notification:settings'), $body);

$all_handlers = _elgg_services()->notifications->getMethods();
$methods = comment_tracker_get_user_notification_methods($user->guid);
?>

<table id="notificationstable" cellspacing="0" cellpadding="4" border="1" width="100%">
	<tr>
		<td>&nbsp;</td>
		<?php
			// Print handler names
			foreach ($all_handlers as $key => $handler) {
				$handler_name = elgg_echo("notification:method:{$handler}");
				echo "<td class=\"{$handler}togglefield\">$handler_name</td>";
				echo "<td class=\"\">&nbsp;</td>";
			}
		?>
	</tr>
	<tr>
		<td class="namefield"><p><?php echo elgg_echo('comment_tracker:notification:settings:how'); ?></p></td>
		<?php
			// Print a checkbox for each notification method
			foreach ($all_handlers as $method) {
				$params = array(
					'name' => "{$method}commentsubscriptions",
				);

				if (in_array($method, $methods)) {
					$params['checked'] = 'checked';
				}

				$checkbox = elgg_view('input/checkbox', $params);
				$icon = "<a id=\"comment{$method}\" class=\"{$method}toggleOff\" onclick=\"adjust{$method}_alt('comment{$method}');\">";

				echo "<td class=\"{$method}togglefield\">{$icon}{$checkbox}</td>";
				echo "<td>&nbsp;</td>";
			}
		?>
	</tr>
</table>
