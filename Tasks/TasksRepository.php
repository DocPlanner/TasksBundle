<?php

namespace DocPlanner\TasksBundle\Tasks;

class TasksRepository
{
	/**
	 * @var BaseTask[]
	 */
	private $tasks = [];
	
	public function add(BaseTask $tag) : self
	{
		$this->tasks[] = $tag;
		
		return $this;
	}
	
	/**
	 * @return BaseTask[]
	 */
	public function getTasks() : array
	{
		return $this->tasks;
	}
}