<?php
/**
 * Notification settings for comment tracker view
 * 
 * @package ElggCommentTracker
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @copyright Copyright (c) 2007-2011 Cubet Technologies. (http://cubettechnologies.com)
 * @version  1.0
 * @author Akhilesh @ Cubet Technologies
 */

global $NOTIFICATION_HANDLERS;
?>
<?php echo elgg_view_title(elgg_echo('comment:notification:settings')); ?>
<div class="contentWrapper">
	<div class="notification_methods">
		<?php echo elgg_view('notifications/subscriptions/jsfuncs',$vars); ?>
		<p>
			<?php echo elgg_echo('comment:notification:settings:description'); ?>
		</p>
		<table id="notificationstable" cellspacing="0" cellpadding="4" border="1" width="100%">
  			<tr>
    			<td>&nbsp;</td>
				<?php
				$i = 0; 
				foreach($NOTIFICATION_HANDLERS as $method => $foo)
				{
					if ($i > 0)
					{
						echo "<td class=\"spacercolumn\">&nbsp;</td>";
					}
				?>
					<td class="<?php echo $method; ?>togglefield"><?php echo elgg_echo('notification:method:'.$method); ?></td>
				<?php
					$i++;
				}
				?>
    			<td>&nbsp;</td>
  			</tr>
			<?php	
			$fields = '';
			$i = 0;
			foreach($NOTIFICATION_HANDLERS as $method => $foo)
			{
				if (!check_entity_relationship(elgg_get_logged_in_user_guid(), 'block_comment_notify' . $method, $CONFIG->site_guid))
				{
					$checked[$method] = 'checked="checked"';
				} 
				else
				{
					$checked[$method] = '';
				}
				
				if ($i > 0) {
					$fields .= "<td class=\"spacercolumn\">&nbsp;</td>";
				}
				
				$fields .= <<< END
					<td class="{$method}togglefield">
					<a border="0" id="comment{$method}" class="{$method}toggleOff" onclick="adjust{$method}_alt('comment{$method}');">
					<input type="checkbox" name="{$method}subscriptions[]" id="{$method}checkbox" onclick="adjust{$method}('comment{$method}');" value="comment" {$checked[$method]} /></a></td>
END;
				$i++;
			}
			?>
			<tr>
				<td class="namefield">
			    	<p>
			    		<?php echo elgg_echo('comments'); ?>
			    	</p>
			    </td>
				<?php echo $fields; ?>
				<td>&nbsp;</td>
			</tr>
		</table>
    <br><br>
    <?php
    echo elgg_view('input/submit', array('value' => elgg_echo('save')));
    ?>
	</div>
</div>