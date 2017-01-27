TasksBundle
============

## Requirements
#### RabbitMQ
`rabbitmq_delayed_message_exchange` plugin enabled

## Installation
#### Composer
```
composer require docplanner/tasks-bundle
```

#### Add to AppKernel
```yaml
	new \OldSound\RabbitMqBundle\OldSoundRabbitMqBundle,
	new DocPlanner\TasksBundle\DocPlannerTasksBundle,
```

## Configuration
### Define connection in `OldSoundRabbitMqBundle` configuration
https://github.com/php-amqplib/RabbitMqBundle
```yaml
old_sound_rabbit_mq:
    connections:
        default:
            host:     "%rabbit.host%"
            port:     "%rabbit.port%"
            user:     "%rabbit.user%"
            password: "%rabbit.password%"
            vhost:    "%rabbit.vhost%"
            lazy:     false
            connection_timeout: 3
            read_write_timeout: 3
            keepalive: false
            heartbeat: 0
```
### Define tasks configuration
```yaml
docplanner_tasks:
    connection_name: default
    names_prefix: asd_
```

## Define task
### Define task payload
You have to extend class `DocPlanner\TasksBundle\Tasks\BaseTaskPayload`, whole object will be serialized using `serialize` function.
### Define job
You have to extend class `DocPlanner\TasksBundle\Tasks\BaseTask`, method `execute` contains job body, method `supports` check that given payload is supported by task.
You have to tag your task service with tag `docplanner_tasks.task`
#### WARNING
If method `execute` will return `true` it means task was executed successfully, `false` will cause requeuing for given task. Requeue number is limited by number defined on task class.
If payload requeue number will exceed requeue limit, task will be moved to bury queue. 

### Examples
```php
class SamplePayload extends BaseTaskPayload
{
	protected $someValue;
	
	public function __construct(string $someValue)
	{
		$this->someValue = $someValue;
	}

	public function getSomeValue() : string 
	{
		return $this->someValue;
	}
}
```

```php
class SampleTask extends BaseTask
{
	/**
	 * @var SamplePayload $payload
	 */
	protected function execute(BaseTaskPayload $payload): bool
	{
		file_put_contents('/tmp/payload.log', var_export($payload, true) . PHP_EOL . $payload->getSomeValue());
		
		return true;
	}
	
	public function supports(BaseTaskPayload $payload): bool
	{
		return $payload instanceof SamplePayload;
	}
}
```

```yaml
  sample_task:
    class: Your\Namespace\SampleTask
    tags:
      - { name: docplanner_tasks.task }
```


## Produce tasks

### Without delay
```php
	$taskProducer = $container->get('docplanner_tasks.task_producer');

	$taskProducer->enqueue(new SamplePayload());
```

### With delay
```php
	$taskProducer = $container->get('docplanner_tasks.task_producer');

	$dateTime = new \DateTime('now + 2 minutes');

	$taskProducer->enqueue(new SamplePayload(), $dateTime);
```

## Consume tasks
```
bin/console docplanner:tasks:run
```
check `--help` for more info