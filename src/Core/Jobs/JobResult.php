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

	public function __construct($status, $nextTimeExecute = null) {
		$this->status = (bool) $status;

		$this->nextTimeExecute = $nextTimeExecute !== null ? Carbon::now()->addMinute($nextTimeExecute) : null;
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
