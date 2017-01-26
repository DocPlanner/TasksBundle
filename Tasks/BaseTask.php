<?php

namespace DocPlanner\TasksBundle\Tasks;

abstract class BaseTask
{
	protected $requeueLimit = 3;

	abstract protected function execute(BaseTaskPayload $payload) : bool;

	abstract public function supports(BaseTaskPayload $payload) : bool;
	
	public function run(BaseTaskPayload $payload)
	{
		if (false === $this->supports($payload))
		{
			throw new \Exception;
		}
		
		return $this->execute($payload);
	}
	
	/**
	 * Determines if the task can be requeued according to internal counter
	 *
	 * @return bool
	 */
	public function canRequeue(BaseTaskPayload $payload)
	{
		if ($payload->getRequeueNumber() > $this->requeueLimit)
		{
			return false;
		}

		return true;
	}
	
	public function setRequeueLimit(int $limit) : self
	{
		$this->requeueLimit = $limit;
		
		return $this;
	}
}