<?php

namespace DocPlanner\TasksBundle\Tasks\Consumer;

use DocPlanner\TasksBundle\Tasks\BaseTask;
use DocPlanner\TasksBundle\Tasks\Consumer\Event\TaskEventInterface;
use DocPlanner\TasksBundle\Tasks\Consumer\Event\TaskExecutedSuccessfullyEvent;
use DocPlanner\TasksBundle\Tasks\Consumer\Event\TaskExecutionFailedEvent;
use DocPlanner\TasksBundle\Tasks\Consumer\Event\TaskPreExecuteEvent;
use DocPlanner\TasksBundle\Tasks\BaseTaskPayload;
use DocPlanner\TasksBundle\Tasks\Exception\GenericTasksException;
use DocPlanner\TasksBundle\Tasks\Exception\MoreThanOneTaskSupportsGivenPayloadException;
use DocPlanner\TasksBundle\Tasks\Exception\NoTasksSupportsGivenPayloadException;
use DocPlanner\TasksBundle\Tasks\TasksRepository;
use Exception;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TaskConsumer implements ConsumerInterface
{
	/** @var EventDispatcherInterface */
	private $dispatcher;
	
	/** @var ProducerInterface */
	private $deadletterProducer;
	
	/** @var ProducerInterface */
	private $buryProducer;
	
	/** @var TasksRepository */
	private $tasksRepository;
	
	public function __construct(TasksRepository $tasksRepository, EventDispatcherInterface $dispatcher, ProducerInterface $deadletterProducer, ProducerInterface $buryProducer)
	{
		$this->tasksRepository    = $tasksRepository;
		$this->dispatcher         = $dispatcher;
		$this->deadletterProducer = $deadletterProducer;
		$this->buryProducer       = $buryProducer;
	}

	/**
	 * @param AMQPMessage $msg The message
	 *
	 * @return mixed false to reject and requeue, any other value to acknowledge
	 */
	public function execute(AMQPMessage $msg)
	{
		try
		{
			/** @var BaseTaskPayload $payload */
			$payload = unserialize($msg->body);
			
			$task = $this->getTask($payload);
		}
		catch (GenericTasksException $exception)
		{
			$this->bury($payload);
			$this->dispatcher->dispatch(TaskEventInterface::TASK_EXECUTION_FAILED, new TaskExecutionFailedEvent($payload, $exception));
			
			return self::MSG_REJECT;
		}
		
		try
		{
			$this->dispatcher->dispatch(TaskEventInterface::TASK_PRE_EXECUTE, new TaskPreExecuteEvent($payload));
			
			if ($task->run($payload))
			{
				$this->dispatcher->dispatch(TaskEventInterface::TASK_EXECUTED_SUCCESSFULLY, new TaskExecutedSuccessfullyEvent($payload));
				
				return self::MSG_ACK;
			}
			
			$this->requeue($task, $payload);
			$this->dispatcher->dispatch(TaskEventInterface::TASK_EXECUTED_UNSUCCESSFULLY, new TaskExecutedSuccessfullyEvent($payload));
			
			return self::MSG_REJECT;
		}
		catch (Exception $e)
		{
			$this->requeue($task, $payload);
			$this->dispatcher->dispatch(TaskEventInterface::TASK_EXECUTION_FAILED, new TaskExecutionFailedEvent($payload, $e));
			
			return self::MSG_REJECT;
		}
	}
	
	private function getTask(BaseTaskPayload $payload) : BaseTask
	{
		$tasks = [];
		foreach ($this->tasksRepository->getTasks() as $task)
		{
			if ($task->supports($payload))
			{
				$tasks[] = $task;
			}
		}
		
		switch (count($tasks))
		{
			case 0:
				throw new NoTasksSupportsGivenPayloadException;
			case 1:
				return $tasks[0];
		}
		
		throw new MoreThanOneTaskSupportsGivenPayloadException;
	}
	
	private function requeue(BaseTask $task, BaseTaskPayload $taskPayload)
	{
		if ($task->canRequeue($taskPayload))
		{
			$this->deadletter($taskPayload);
		}
		else
		{
			$this->bury($taskPayload);
		}
	}
	
	private function deadletter(BaseTaskPayload $taskPayload)
	{
		$taskPayload->incrementRequeueNumber();
		$this->deadletterProducer->publish($taskPayload);
	}
	
	private function bury(BaseTaskPayload $taskPayload)
	{
		$taskPayload->incrementBuryNumber();
		$this->buryProducer->publish($taskPayload);
	}
}