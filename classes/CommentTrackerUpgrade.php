<?php

abstract class CommentTrackerUpgrade {
	/**
	 * @var boolean Is this upgrade run once or in several batches
	 */
	protected $is_batch_upgrade;

	/**
	 * @var int The offset of targets being upgraded
	 */
	protected $offset = 0;

	/**
	 *
	 */
	protected $success_count = 0;

	/**
	 *
	 */
	public function isBatchUpgrade() {
		return $this->is_batch_upgrade;
	}

	/**
	 *
	 */
	abstract public function countObjects();

	/**
	 *
	 */
	public function setOffset($offset) {
		$this->offset = $offset;
	}

	/**
	 *
	 */
	public function getOffset() {
		return (int) $this->offset;
	}

	/**
	 *
	 */
	public function getSuccessCount() {
		return (int) $this->success_count;
	}
}