<?php

namespace DocPlanner\TasksBundle\Tasks\Consumer\Event;

use DocPlanner\TasksBundle\Tasks\BaseTaskPayload;

interface TaskEventInterface
{
	const TASK_PRE_EXECUTE = 'task.pre.execute';
	const TASK_EXECUTED_SUCCESSFULLY = 'task.executed.successfully';
	const TASK_EXECUTED_UNSUCCESSFULLY = 'task.executed.unsuccessfully';
	const TASK_EXECUTION_FAILED = 'task.execution.failed';
	
	public function getPayload() : BaseTaskPayload;
}