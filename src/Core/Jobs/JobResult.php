<?php
namespace GameX\Core\Jobs;

use \Carbon\Carbon;

class JobResult {

	/**
	 * @var bool
	 */
	protected $status;

	/**
	 * @var Carbon|null
	 */
	protected $nextTimeExecute = null;

	/**
	 * JobResult constructor.
	 * @param bool $status
	 * @param Carbon|null $nextTimeExecute
	 */
	public function __construct($status, Carbon $nextTimeExecute = null) {
		$this->status = (bool) $status;
		$this->nextTimeExecute = $nextTimeExecute;
	}

	public function getStatus() {
		return $this->status;
	}

	/**
	 * @return Carbon|null
	 */
	public function getNextTimeExecute() {
		return $this->nextTimeExecute;
	}
}
