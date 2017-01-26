<?php

namespace DocPlanner\TasksBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class TasksCompilerPass implements CompilerPassInterface
{
	const TAG = 'docplanner_tasks.task';
	
    public function process(ContainerBuilder $container)
    {
		$taskList = $container->findTaggedServiceIds(self::TAG);
		if (0 === count($taskList))
		{
			return;
		}
		
		$repositoryDefinition = $container->findDefinition('docplanner_tasks.tasks_repository');
		
		foreach ($taskList as $taskServiceId => $_)
		{
			$repositoryDefinition->addMethodCall('add', [new Reference($taskServiceId)]);
		}
    }
}
