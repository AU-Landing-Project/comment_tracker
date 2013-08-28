<?php
/**
 * Upgrade subscribtion relationships
 * 
 * The old way was to save a "comment_subscribe" relationship between user
 * and the entity being followed. Notification methods were determined from
 * settings when sending notifications. The new way is to save the method
 * within the name of the relationship in the form "notify<method>" which is
 * then used by the Elgg 1.9 core notifications system.
 */
class upgrades_CommentTrackerUpgrade2 {
	private $relationship;
	
	public function __construct() {
		$this->is_batch_upgrade = true;
		$relationship = 'comment_subscribe';
	}

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

	public function run() {
		$query = "SELECT * FROM {$dbprefix}entity_relationships ".
				  "WHERE relationship = '{$this->relationship}' ".
				  "LIMIT({$this->limit}, {$this->offset})";

		$relationships = get_data($query, "row_to_elggrelationship");

		foreach ($relationships as $relationship) {
			$user_guid = $relationship->guid_one;
			$object_guid = $relationship->guid_two;

			// TODO User settings need to be upgrade before this function can be used!
			$methods = comment_tracker_get_user_notification_methods($user_guid);

			foreach ($methods as $method) {
				add_entity_relationship($user_guid, "notify{$method}", $object_guid);
			}

			$relationships_to_delete[] = $relationship->id;
			$count = true;
		}

		if ($relationships_to_delete) {
			$relationship_ids = implode(",", $relationships_to_delete);
			$delete_query = "DELETE FROM {$dbprefix}entity_relationships WHERE id IN ($relationship_ids)";
			delete_data($delete_query);
		}
	}
}