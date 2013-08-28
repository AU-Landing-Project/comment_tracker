<?php

abstract class CommentTrackerUpgrade {
	/**
	 * @var boolean Is this upgrade run once or in several batches
	 */
	protected $is_batch_upgrade;

	/**
	 * @var boolean Does the upgrade need to keep track with offset
	 */
	protected $is_repeating;

	/**
	 * @var int The offset of targets being upgraded
	 */
	protected $offset;

	/**
	 * 
	 */
	public function isBatchUpgrade() {
		return $this->is_batch_upgrade;
	}

	/**
	 * 
	 */ 
	public function countObjects() {
		return false;
	}

	/**
	 * 
	 */
	public function setOffset($offset) {
		$this->offset = $offset;
	}
}