<?php

namespace DocPlanner\TasksBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\DependencyInjection\Loader;

class DocPlannerTasksExtension extends ConfigurableExtension implements PrependExtensionInterface
{
	/** {@inheritdoc} */
	protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
	{
		$loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
		$loader->load('services.yml');
	}
	
	/** {@inheritdoc} */
	public function getConfiguration(array $config, ContainerBuilder $container)
	{
		return new Configuration($this->getAlias());
	}

	public function getAlias()
	{
		return 'docplanner_tasks';
	}
	
	/**
	 * Allow an extension to prepend the extension configurations.
	 *
	 * @param ContainerBuilder $container
	 */
	public function prepend(ContainerBuilder $container)
	{
		$configs = $container->getExtensionConfig($this->getAlias());
		$myConfig = $this->processConfiguration(new Configuration($this->getAlias()), $configs);
		
		$connectionName = $myConfig['connection_name'];
		$deadletterTtl  = $myConfig['deadletter_ttl'];
		$namesPrefix    = $myConfig['names_prefix'];
		
		$asd = [
			'producers' => [
				$namesPrefix . 'task'            => [
					'connection'       => $connectionName,
					'exchange_options' => [
						'name'           => $namesPrefix . 'task',
						'type'           => 'x-delayed-message',
						'arguments'		 => [
							'x-delayed-type' => ['S', 'topic'],
						],
					],
					'queue_options'    => [
						'name' => $namesPrefix . 'task',
						'routing_keys' => [
							'#',
						]
					],
				],
				$namesPrefix . 'task_deadletter' => [
					'connection'       => $connectionName,
					'exchange_options' => [
						'name' => $namesPrefix . 'task.deadletter',
						'type' => 'topic',
					],
					'queue_options'    => [
						'name'      => $namesPrefix . 'task.deadletter',
						'arguments' => [
							'x-message-ttl'          => [
								0 => 'I',
								1 => $deadletterTtl,
							],
							'x-dead-letter-exchange' => [
								0 => 'S',
								1 => $namesPrefix . 'task',
							],
						],
						'routing_keys' => [
							'#',
						],
					],
				],
				$namesPrefix . 'task_bury'       => [
					'connection'       => $connectionName,
					'exchange_options' => [
						'name' => $namesPrefix . 'task.bury',
						'type' => 'topic',
					],
					'queue_options'    => [
						'name' => $namesPrefix . 'task.bury',
						'routing_keys' => [
							'#',
						],
					],
				],
			],
			'consumers' => [
				$namesPrefix . 'task' => [
					'connection'       => $connectionName,
					'exchange_options' => [
						'name' => $namesPrefix . 'task',
						'type' => 'x-delayed-message',
					],
					'queue_options'    => [
						'name' => $namesPrefix . 'task',
						'routing_keys' => [
							'#',
						],
					],
					'callback'         => 'docplanner_tasks.task_consumer',
					'idle_timeout'     => 300,
					'qos_options'      => [
						'prefetch_size'  => 0,
						'prefetch_count' => 1,
						'global'         => false,
					],
				],
			],
		];
		
		$container->prependExtensionConfig('old_sound_rabbit_mq', $asd);
		
		$container->setAlias('docplanner_tasks.internal.task_producer', sprintf('old_sound_rabbit_mq.%stask_producer', $namesPrefix));
		$container->setAlias('docplanner_tasks.internal.task_deadletter_producer', sprintf('old_sound_rabbit_mq.%stask_deadletter_producer', $namesPrefix));
		$container->setAlias('docplanner_tasks.internal.task_bury_producer', sprintf('old_sound_rabbit_mq.%stask_bury_producer', $namesPrefix));
		
		$container->setParameter('docplanner_tasks.tasks_consumer_name', $namesPrefix . 'task');
	}
}
