<?php
/**
 * Display a form for choosing notification methods
 */

$entity = $vars['entity'];

// The form values go here
$body_vars = array(
	'guid' => $entity->guid,
	'user_guid' => elgg_get_logged_in_user_guid(),
);

echo elgg_view_form('comment_tracker/manage', $form_vars = array(), $body_vars);
