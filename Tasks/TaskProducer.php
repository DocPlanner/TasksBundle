<?php

namespace DocPlanner\TasksBundle\Tasks;

use OldSound\RabbitMqBundle\RabbitMq\Producer;

class TaskProducer
{
	/** @var Producer */
	protected $producer;
	
	public function __construct(Producer $producer)
	{
		$this->producer = $producer;
	}
	
	public function enqueue(BaseTaskPayload $payload, \DateTimeInterface $executeAfterDateTime = null, string $routingKey = '')
	{
		$headers = [];
		if (null !== $executeAfterDateTime)
		{
			$now = \DateTime::createFromFormat('U.u', microtime(true));
			$diff = $executeAfterDateTime->format('Uu') - $now->format('Uu');
			if ($diff > 0)
			{
				$headers['x-delay'] = (int) ($diff/1000);
			}
		}
		
		$this->producer->publish($payload, $routingKey, [], $headers);
	}
}