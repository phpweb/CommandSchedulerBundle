<?php

namespace Dukecity\CommandSchedulerBundle\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;
use Dukecity\CommandSchedulerBundle\Event\SchedulerCommandCreatedEvent;
use Dukecity\CommandSchedulerBundle\Event\SchedulerCommandPostExecutionEvent;
use Dukecity\CommandSchedulerBundle\Event\SchedulerCommandFailedEvent;
use Dukecity\CommandSchedulerBundle\Event\SchedulerCommandPreExecutionEvent;
use Dukecity\CommandSchedulerBundle\Notification\CronMonitorNotification;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;

class SchedulerCommandSubscriber implements EventSubscriberInterface
{
    protected LoggerInterface $logger;
    protected EntityManagerInterface $em;
    protected ContainerInterface $container;
    protected NotifierInterface|null $notifier;

    /**
     * TODO check if parameters needed
     * SchedulerCommandSubscriber constructor.
     *
     * @param ContainerInterface     $container
     * @param LoggerInterface        $logger
     * @param EntityManagerInterface $em
     * @param NotifierInterface|null $notifier
     * @param array                  $monitor_mail
     * @param string                 $monitor_mail_subject
     */
    public function __construct(ContainerInterface $container, LoggerInterface $logger, EntityManagerInterface $em, NotifierInterface|null $notifier = null, private array $monitor_mail = [], private string $monitor_mail_subject = 'CronMonitor:')
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->em = $em;
        $this->notifier = $notifier;
    }

    /**
     * {@inheritdoc}
     */
    #[ArrayShape([
        SchedulerCommandCreatedEvent::class => 'array',
        SchedulerCommandFailedEvent::class => 'array',
        SchedulerCommandPreExecutionEvent::class => 'array',
        SchedulerCommandPostExecutionEvent::class => 'array',
    ])]
    public static function getSubscribedEvents(): array
    {
        return [
            SchedulerCommandCreatedEvent::class => ['onScheduledCommandCreated',    -10],
            SchedulerCommandFailedEvent::class => ['onScheduledCommandFailed',     20],
            SchedulerCommandPreExecutionEvent::class => ['onScheduledCommandPreExecution',   10],
            SchedulerCommandPostExecutionEvent::class => ['onScheduledCommandPostExecution',   30],
        ];
    }

    // TODO check if useful (could be handled by doctrine lifecycle events)
    public function onScheduledCommandCreated(SchedulerCommandCreatedEvent $event)
    {
        $this->logger->info('ScheduledCommandCreated', ['name' => $event->getCommand()->getName()]);
    }

    public function onScheduledCommandFailed(SchedulerCommandFailedEvent $event)
    {
        # notifier is optional
        if($this->notifier)
        {
            //...$this->notifier->getAdminRecipients()
            $recipients = [];
            foreach ($this->monitor_mail as $mailadress) {
                $recipients[] = new Recipient($mailadress);
            }

            $this->notifier->send(new CronMonitorNotification($event->getFailedCommands(), $this->monitor_mail_subject), ...$recipients);
        }

        //$this->logger->warning('SchedulerCommandFailedEvent', ['details' => $event->getMessage()]);
    }

    public function onScheduledCommandPreExecution(SchedulerCommandPreExecutionEvent $event)
    {
        #var_dump('ScheduledCommandPreExecution');
        $this->logger->info('ScheduledCommandPreExecution', ['name' => $event->getCommand()->getName()]);
    }

    public function onScheduledCommandPostExecution(SchedulerCommandPostExecutionEvent $event)
    {
        #var_dump('ScheduledCommandPostExecution');

        $this->logger->info('ScheduledCommandPostExecution', [
            'name' => $event->getCommand()->getName(),
            "result" => $event->getResult(),
            #"log" => $event->getLog(),
            "runtime" => $event->getRuntime()->format('%S seconds'),
            #"exception" => $event->getException()?->getMessage() ?? null
        ]);
    }
}
