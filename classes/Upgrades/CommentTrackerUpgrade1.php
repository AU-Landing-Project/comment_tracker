<?php
/**
 * Upgrade how personal notification settings are stored
 *
 * The old way was to save the relationship "block_comment_notify<method>"
 * between user and site for the methods that user did not want to use.
 * The new way will save the enabled methods as a serialized array to plugin
 * usersettings.
 */
class Upgrades_CommentTrackerUpgrade1 extends CommentTrackerUpgrade {

	public function __construct() {
		$this->is_batch_upgrade = true;
		$this->limit = 10;
	}

	/**
	 * Get the total number of objects that need to be upgraded
	 *
	 * @return int
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
	 * Run the upgrade
	 */
	public function run() {
		$relationships_to_delete = array();
		$dbprefix = elgg_get_config('dbprefix');

		// Get the users who have saved settings for comment_tracker
		$users = elgg_get_entities_from_relationship(array(
			'type' => 'user',
			'limit' => $this->limit,
			'joins' => array("JOIN {$dbprefix}entity_relationships er ON er.guid_one = e.guid"),
			'wheres' => array("relationship LIKE 'block_comment_notify%'"),
		));

		foreach ($users as $user) {
			// We process all settings of one user at a time
			$query = "SELECT * FROM {$dbprefix}entity_relationships ".
					 "WHERE guid_one='{$user->guid} '".
					 "AND relationship LIKE 'block_comment_notify%'";

			$relationships = get_data($query, "row_to_elggrelationship");

			$methods_to_save = array();
			foreach ($relationships as $relationship) {
				// Get the notification method from the relationship "block_comment_notify<method>"
				$methods_to_save[] = str_replace('block_comment_notify', '', $relationship->relationship);

				// The relationships are deleted later in a single query
				$relationships_to_delete[] = $relationship->id;

				// We count relationships instead of users because they provide
				// a more realistic data for the upgrade progress bar
				$this->success_count++;
			}

			// Save the settings
			$saved = comment_tracker_set_user_notification_methods($methods_to_save, $relationship->guid_one);

			if ($saved) {
				// The old relationships can now be deleted
				if ($relationships_to_delete) {
					$relationship_ids = implode(",", $relationships_to_delete);
					$delete_query = "DELETE FROM {$dbprefix}entity_relationships WHERE id IN ($relationship_ids)";
					delete_data($delete_query);
				}
			} else {
				register_error(elgg_echo('comment_tracker:upgrade:usersettings_failure', array($user->getDisplayName, $user->guid)));
			}
		}
	}
}
