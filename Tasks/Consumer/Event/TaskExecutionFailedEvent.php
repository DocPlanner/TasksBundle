<?php

namespace DocPlanner\TasksBundle\Tasks\Consumer\Event;

use DocPlanner\TasksBundle\Tasks\BaseTaskPayload;

class TaskExecutionFailedEvent extends BaseTaskEvent
{
	/** @var \Exception */
	private $exception;
	
	public function __construct(BaseTaskPayload $payload, \Exception $exception)
	{
		parent::__construct($payload);
		
		$this->exception = $exception;
	}
	
	/**
	 * @return \Exception
	 */
	public function getException()
	{
		return $this->exception;
	}
}