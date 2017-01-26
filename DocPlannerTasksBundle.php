<?php

namespace DocPlanner\TasksBundle;

use DocPlanner\TasksBundle\DependencyInjection\Compiler\TasksCompilerPass;
use DocPlanner\TasksBundle\DependencyInjection\DocPlannerTasksExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DocPlannerTasksBundle extends Bundle
{
	public function getContainerExtension()
	{
		return new DocPlannerTasksExtension();
	}
	
	public function build(ContainerBuilder $container)
	{
		parent::build($container);
		$container->addCompilerPass(new TasksCompilerPass());
	}
}
