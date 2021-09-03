<?php

namespace Dukecity\CommandSchedulerBundle\Event;

use JetBrains\PhpStorm\Pure;
use Dukecity\CommandSchedulerBundle\Entity\ScheduledCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Error;

class SchedulerCommandPostExecutionEvent extends AbstractSchedulerCommandEvent
{
    /**
     * List of failed commands.
     *
     * @param ScheduledCommand $command
     * @param int $result
     * @param OutputInterface|null $log
     * @param array|null $profiling
     * @param \Exception|Error|null $exception
     */
    #[Pure]
    public function __construct(
        private ScheduledCommand $command,
        private int $result,
        private ?OutputInterface $log = null,
        private ?array $profiling = null,
        private \Exception|Error|null $exception = null)
    {
        parent::__construct($command);
    }

    /**
     * @return int
     */
    public function getResult(): int
    {
        return $this->result;
    }

    /**
     * @return OutputInterface|null
     */
    public function getLog(): ?OutputInterface
    {
        return $this->log;
    }

    public function getProfiling(): ?array
    {
        return $this->profiling;
    }

    public function getRuntime(): ?\DateInterval
    {
        return $this->profiling["runtime"] ?? null;
    }

    /**
     * @return \Exception|Error|null
     */
    public function getException(): \Exception|Error|null
    {
        return $this->exception;
    }

}
