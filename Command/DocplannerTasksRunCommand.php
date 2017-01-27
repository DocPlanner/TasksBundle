<?php

namespace DocPlanner\TasksBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DocplannerTasksRunCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
			->setName('docplanner:tasks:run')
			->setDescription('Run task worker')
			->addOption('messages', 'm', InputOption::VALUE_OPTIONAL, 'Messages to consume', 0)
			->addOption('route', 'r', InputOption::VALUE_OPTIONAL, 'Routing Key', '')
			->addOption('memory-limit', 'l', InputOption::VALUE_OPTIONAL, 'Allowed memory for this process', null)
			->addOption('debug', 'd', InputOption::VALUE_NONE, 'Enable Debugging')
			->addOption('without-signals', 'w', InputOption::VALUE_NONE, 'Disable catching of system signals');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
		$command = $this->getApplication()->find('rabbitmq:consumer');
		
		$definition = new InputDefinition();
		$definition->addArgument(new InputArgument('name', InputArgument::REQUIRED));
		$definition->addOption(new InputOption('messages', 'm', InputOption::VALUE_OPTIONAL, 'Messages to consume', 0));
		$definition->addOption(new InputOption('route', 'r', InputOption::VALUE_OPTIONAL, 'Routing Key', ''));
		$definition->addOption(new InputOption('memory-limit', 'l', InputOption::VALUE_OPTIONAL, 'Allowed memory for this process', null));
		$definition->addOption(new InputOption('debug', 'd', InputOption::VALUE_NONE, 'Enable Debugging'));
		$definition->addOption(new InputOption('without-signals', 'w', InputOption::VALUE_NONE, 'Disable catching of system signals'));
			
		$arrayInput = new ArrayInput(['name' => $this->getContainer()->getParameter('docplanner_tasks.tasks_consumer_name')], $definition);
		$arrayInput->setOption('messages', $input->getOption('messages'));
		$arrayInput->setOption('route', $input->getOption('route'));
		$arrayInput->setOption('memory-limit', $input->getOption('memory-limit'));
		$arrayInput->setOption('debug', $input->getOption('debug'));
		$arrayInput->setOption('without-signals', $input->getOption('without-signals'));
	
		return $command->run($arrayInput, $output);
    }
}
