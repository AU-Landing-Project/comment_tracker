<?php
/**
 * Manage unsubscribe in comment tracker plugin
 * 
 * @package ElggCommentTracker
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @copyright Copyright (c) 2007-2011 Cubet Technologies. (http://cubettechnologies.com)
 * @version  1.0
 * @author Akhilesh @ Cubet Technologies
 */
$entity = $vars['entity'];
?>
<div class="comment_trackerWrapper">
	<?php if(check_entity_relationship(elgg_get_logged_in_user_guid(), 'comment_subscribe', $entity->guid)){?>
		<form action="<?php echo elgg_get_site_url(); ?>action/comment_tracker/unsubscribe" method="post">
      <?php
      echo elgg_view('input/submit', array('name' => 'unsubscribe', 'value' => elgg_echo('comment:unsubscribe')));
      echo elgg_view('input/securitytoken'); ?>
			<input type="hidden" name="entity_guid" value="<?php echo $entity->guid?>" />
		</form>
	<?php } else {?>
		<form action="<?php echo elgg_get_site_url(); ?>action/comment_tracker/subscribe" method="post">
      <?php
      echo elgg_view('input/submit', array('name' => 'unsubscribe', 'value' => elgg_echo('comment:subscribe')));
      echo elgg_view('input/securitytoken'); ?>
			<input type="hidden" name="entity_guid" value="<?php echo $entity->guid?>" />
		</form>
	<?php }?>
</div>