<?php

namespace DocPlanner\TasksBundle\Tasks;

abstract class BaseTaskPayload
{
	protected $requeueNumber = 0;
	
	protected $buryNumber = 0;
	
	public function getRequeueNumber(): bool
	{
		return $this->requeueNumber;
	}
	
	public function getBuryNumber(): bool
	{
		return $this->buryNumber;
	}
	
	public function incrementRequeueNumber() : self
	{
		++$this->requeueNumber;
		
		return $this;
	}
	
	public function incrementBuryNumber() : self
	{
		++$this->buryNumber;
		
		return $this;
	}
	
	final public function __toString()
	{
		return serialize($this);
	}
}