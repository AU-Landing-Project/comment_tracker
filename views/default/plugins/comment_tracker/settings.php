<?php
/**
 * Comment tracker plugin settings
 */

$allow_comment_notification = $vars['entity']->allow_comment_notification;
if (!$allow_comment_notification) $allow_comment_notification = 'yes';

?>	
<p style="margin-bottom:10px;">
	<?php 
		echo elgg_echo('comment_tracker:notifications:enable');
		echo elgg_view('input/dropdown', array(
			'name' => 'params[allow_comment_notification]',
			'options_values' => array(
				'no' => elgg_echo('option:no'),
				'yes' => elgg_echo('option:yes')
			),
			'value' => $allow_comment_notification
		));
	?>
</p>

<p>
	<?php 
		echo elgg_echo('comment_tracker:setting:notify_owner');
		echo elgg_view('input/dropdown', array(
			'name' => 'params[notify_owner]',
			'options_values' => array(
				'yes' => elgg_echo('option:yes'),
				'no' => elgg_echo('option:no')
			),
			'value' => $vars['entity']->notify_owner ? $vars['entity']->notify_owner : 'no'
		));
	?>
</p>

<p>
	<?php 
		echo elgg_echo('comment_tracker:setting:show_button');
		echo elgg_view('input/dropdown', array(
			'name' => 'params[show_button]',
			'options_values' => array(
				'yes' => elgg_echo('option:yes'),
				'no' => elgg_echo('option:no')
			),
			'value' => $vars['entity']->show_button ? $vars['entity']->show_button : 'no'
		));
	?>
</p>