<?php
/**
 * Upgrade how personal notification settings are stored
 * 
 * The old way was to save the relationship "block_comment_notify<method>"
 * between user and site for the methods that user did not want to use.
 * The new way will save the enabled methods as a serialized array to plugin
 * usersettings.
 */
class upgrades_CommentTrackerUpgrade1 extends CommentTrackerUpgrade {
	/**
	 * 
	 */
	public function __construct() {
		$this->is_batch_upgrade = true;
		$this->limit = 10;
	}

	/**
	 * 
	 */
	public function countObjects() {
		$dbprefix = elgg_get_config('dbprefix');
		$query = "SELECT count(id) count FROM {$dbprefix}entity_relationships WHERE relationship LIKE 'block_comment_notify%'";
		$data = get_data($query);

		if (isset($data[0])) {
			return $data[0]->count;
		} else {
			return 0;
		}
	}

	/**
	 * 
	 */
	public function run() {
		$relationships_to_delete = array();
		$notification_handlers = _elgg_services()->notifications->getMethods();

		$users = elgg_get_entities(array(
			'type' => $user,
			'limit' => $this->limit,
			'offset' => $this->offset,
		));

		foreach ($users as $user) {
			$query = "SELECT * FROM {$dbprefix}entity_relationships".
					 "WHERE guid_one='{$user->guid}'".
					 "AND relationship LIKE 'block_comment_notify%'";

			$relationships = get_data($query, "row_to_elggrelationship");

			/**
			 * This equals as "enabled setting":
			 * remove_entity_relationship($user->guid, 'block_comment_notify'.$method, elgg_get_site_entity()->guid); 
			 */
			foreach ($relationships as $relationship) {
				echo "<p>Processing row: {$relationship->guid_one} $relationship->relationship {$relationship->guid_two}</p>";
				$disabled_method = str_replace('block_comment_notify', '', $relationship->relationship);
				echo "<p>Method to be disabled: $disabled_method</p>";

				$enabled_methods = $notification_handlers;
				foreach ($notification_handlers as $key => $notification_handler) {
					// Remove method from the array if it has been explicitly disabled
					if ($disabled_method == $notification_handler) {
						unset($enabled_methods[$key]);
					}
				}

				$enabled_methods = implode(', ', $enabled_methods);
				echo "<p>Enabled methods: $enabled_methods</p>";
				echo "<br /><hr />";

				//comment_tracker_set_user_notification_settings($enabled_methods, $relationship->guid_one);
			}
		}
	}
}