<?php
/**
 * Upgrade subscription relationships
 *
 * The old way was to save a "comment_subscribe" relationship between user
 * and the entity being followed. Notification methods were determined from
 * settings when sending notifications. The new way is to save the method
 * within the name of the relationship in the form "notify<method>" which is
 * then used by the Elgg 1.9 core notifications system.
 */
class Upgrades_CommentTrackerUpgrade2 extends CommentTrackerUpgrade {
	private $relationship;

	public function __construct() {
		$this->is_batch_upgrade = true;
		$this->limit = 50;
		$this->relationship = 'comment_subscribe';
	}

	/**
	 * Get the total amount of objects that need to be upgraded
	 *
	 * @return int
	 */
	public function countObjects() {
		$dbprefix = elgg_get_config('dbprefix');
		$query = "SELECT count(id) as count FROM {$dbprefix}entity_relationships WHERE relationship = '{$this->relationship}'";
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
		$dbprefix = get_config('dbprefix');
		$query = "SELECT * FROM {$dbprefix}entity_relationships ".
				  "WHERE relationship = '{$this->relationship}' ".
				  "LIMIT {$this->limit}";

		$relationships = get_data($query, "row_to_elggrelationship");

		foreach ($relationships as $relationship) {
			$user_guid = $relationship->guid_one;
			$object_guid = $relationship->guid_two;

			$methods = comment_tracker_get_user_notification_methods($user_guid);

			if (!$methods) {
				$this->success_count++;
				$relationships_to_delete[] = $relationship->id;
				continue;
			}

			foreach ($methods as $method) {
				// Save in the format used by the new Elgg 1.9 notifications system
				if (add_entity_relationship($user_guid, "notify{$method}", $object_guid)) {
					$relationships_to_delete[] = $relationship->id;
				}
			}

			$this->success_count++;

		}

		// The successfully upgraded relationships can now be deleted
		if ($relationships_to_delete) {
			$relationship_ids = implode(",", $relationships_to_delete);
			$delete_query = "DELETE FROM {$dbprefix}entity_relationships WHERE id IN ($relationship_ids)";
			delete_data($delete_query);
		}
	}
}
