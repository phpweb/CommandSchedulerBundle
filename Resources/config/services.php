<?php

declare(strict_types=1);

use Dukecity\CommandSchedulerBundle\Command\AddCommand;
use Dukecity\CommandSchedulerBundle\Command\ExecuteCommand;
use Dukecity\CommandSchedulerBundle\Command\MonitorCommand;
use Dukecity\CommandSchedulerBundle\Command\RemoveCommand;
use Dukecity\CommandSchedulerBundle\Command\StartSchedulerCommand;
use Dukecity\CommandSchedulerBundle\Command\StopSchedulerCommand;
use Dukecity\CommandSchedulerBundle\Command\TestCommand;
use Dukecity\CommandSchedulerBundle\Command\UnlockCommand;
use Dukecity\CommandSchedulerBundle\Controller\DetailController;
use Dukecity\CommandSchedulerBundle\Controller\ApiController;
use Dukecity\CommandSchedulerBundle\Controller\ListController;
use Dukecity\CommandSchedulerBundle\Entity\ScheduledCommand;
use Dukecity\CommandSchedulerBundle\EventSubscriber\SchedulerCommandSubscriber;
use Dukecity\CommandSchedulerBundle\Form\Type\CommandChoiceType;
use Dukecity\CommandSchedulerBundle\Service\CommandParser;
use Dukecity\CommandSchedulerBundle\Command\ListCommand;
use Dukecity\CommandSchedulerBundle\Service\CommandSchedulerExecution;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;


# TODO In versions earlier to Symfony 5.1 the service() function was called ref()
# Stay with ref() for symfony 4.4 support
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DetailController::class)
        ->public()
        ->autowire()
        ->call('setManagerName', ['%dukecity_command_scheduler.doctrine_manager%'])
        ->call('setTranslator', [ref('translator')])
        ->tag('container.service_subscriber')
        ->tag('controller.service_arguments');

    $services->set(ListController::class)
        ->public()
        ->autowire()
        ->call('setManagerName', ['%dukecity_command_scheduler.doctrine_manager%'])
        ->call('setTranslator', [ref('translator')])
        ->call('setLockTimeout', ['%dukecity_command_scheduler.lock_timeout%'])
        ->call('setLogger', [ref('logger')])
        ->tag('container.service_subscriber')
        ->tag('controller.service_arguments');



    $services->set(CommandParser::class)
        ->args(
            [
                ref('kernel'),
                '%dukecity_command_scheduler.excluded_command_namespaces%',
                '%dukecity_command_scheduler.included_command_namespaces%',
            ]
        );

    $services->set(ApiController::class)
        ->public()
        ->autowire()
        ->call('setManagerName', ['%dukecity_command_scheduler.doctrine_manager%'])
        ->call('setTranslator', [ref('translator')])
        ->call('setLockTimeout', ['%dukecity_command_scheduler.lock_timeout%'])
        ->call('setLogger', [ref('logger')])
        ->call('setCommandParser', [ref(CommandParser::class)])
        ->tag('controller.service_arguments');

    $services->set(CommandSchedulerExecution::class)
        ->args(
            [
                ref('kernel'),
                ref('service_container'),
                ref('logger'),
                ref('event_dispatcher'),
                ref('doctrine'),
                '%dukecity_command_scheduler.doctrine_manager%',
                '%dukecity_command_scheduler.log_path%',
            ]
        )
        #->alias("CommandSchedulerExecution")
    ;

    $services->set(CommandChoiceType::class)
        ->autowire()
        ->tag('form.type', ['alias' => 'command_choice']);

    $services->set(ExecuteCommand::class)
        ->args(
            [
                ref('Dukecity\CommandSchedulerBundle\Service\CommandSchedulerExecution'),
                ref('event_dispatcher'),
                ref('doctrine'),
                '%dukecity_command_scheduler.doctrine_manager%',
                '%dukecity_command_scheduler.log_path%',
            ]
        )
        ->tag('console.command');

    $services->set(MonitorCommand::class)
        ->args(
            [
                ref('event_dispatcher'),
                ref('doctrine'),
                '%dukecity_command_scheduler.doctrine_manager%',
                '%dukecity_command_scheduler.lock_timeout%',
                '%dukecity_command_scheduler.monitor_mail%',
                '%dukecity_command_scheduler.monitor_mail_subject%',
                '%dukecity_command_scheduler.send_ok%',
            ]
        )
        ->tag('console.command');

    $services->set(ListCommand::class)
        ->args(
            [
                ref('doctrine'),
                '%dukecity_command_scheduler.doctrine_manager%'
            ]
        )
        ->tag('console.command');

    $services->set(UnlockCommand::class)
        ->args(
            [
                ref('doctrine'),
                '%dukecity_command_scheduler.doctrine_manager%',
                '%dukecity_command_scheduler.lock_timeout%',
            ]
        )
        ->tag('console.command');

    $services->set(AddCommand::class)
        ->args(
            [
                ref('doctrine'),
                '%dukecity_command_scheduler.doctrine_manager%',
            ]
        )
        ->tag('console.command');

    $services->set(RemoveCommand::class)
        ->args(
            [
                ref('doctrine'),
                '%dukecity_command_scheduler.doctrine_manager%',
            ]
        )
        ->tag('console.command');

    $services->set(StartSchedulerCommand::class)
        ->tag('console.command');

    $services->set(StopSchedulerCommand::class)
        ->tag('console.command');

    $services->set(TestCommand::class)
        ->tag('console.command');

    $services->set(ScheduledCommand::class)
        ->tag('controller.service_arguments');


    if(class_exists(Symfony\Component\Notifier\NotifierInterface::class))
    {$notifier = ref('notifier');}
    else { $notifier = null; }

    $services->set(SchedulerCommandSubscriber::class)
        ->args(
            [
                ref('service_container'),
                ref('logger'),
                ref('doctrine.orm.default_entity_manager'),
                $notifier,
                '%dukecity_command_scheduler.monitor_mail%',
                '%dukecity_command_scheduler.monitor_mail_subject%',
            ]
        )
        ->tag('kernel.event_subscriber');
};
