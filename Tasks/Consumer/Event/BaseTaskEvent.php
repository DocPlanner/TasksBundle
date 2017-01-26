<?php

namespace DocPlanner\TasksBundle\Tasks\Consumer\Event;

use DocPlanner\TasksBundle\Tasks\BaseTaskPayload;
use Symfony\Component\EventDispatcher\Event;

abstract class BaseTaskEvent extends Event implements TaskEventInterface
{
	/** @var BaseTaskPayload */
	protected $payload;
	
	public function __construct(BaseTaskPayload $payload)
	{
		$this->payload = $payload;
	}
	
	public function getPayload(): BaseTaskPayload
	{
		return $this->payload;
	}
}