<?php

namespace Dukecity\CommandSchedulerBundle\Entity;

use Carbon\Carbon;
use Cron\CronExpression as CronExpressionLib;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Dukecity\CommandSchedulerBundle\Repository\ScheduledCommandRepository;
use JetBrains\PhpStorm\Pure;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Dukecity\CommandSchedulerBundle\Validator\Constraints as AssertDukecity;

/**
 * https://www.doctrine-project.org/2021/05/24/orm2.9.html
 * @author  Julien Guyon <julienguyon@hotmail.com>
 */
#[ORM\Entity(repositoryClass: ScheduledCommandRepository::class)]
#[ORM\Table(name: "scheduled_command")]
#[UniqueEntity(fields: ["name"])]
class ScheduledCommand
{
    #[ORM\Id, ORM\Column(type: Types::INTEGER), ORM\GeneratedValue(strategy: 'AUTO')]
    private $id; # temporary, otherwise EasyAdminBundle could not create new entries
    #private ?int $id = null;

    // see https://www.doctrine-project.org/projects/doctrine-orm/en/2.9/reference/transactions-and-concurrency.html
    #[ORM\Version()]
    #[ORM\Column(type: Types::INTEGER)]
    private int $version = 0;

    #[ORM\Column(name: "created_at", type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $createdAt = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING, length: 150, unique: true, nullable: false)]
    private string $name;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING, length: 200, nullable: false)]
    private string $command;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $arguments = null;

    /**
     * @see http://www.abunchofutils.com/utils/developer/cron-expression-helper/
     */
    #[Assert\NotBlank]
    #[AssertDukecity\CronExpression]
    #[ORM\Column(type: Types::STRING, length: 200, nullable: true)]
    private string $cronExpression;

    #[Assert\Type(DateTime::class)]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $lastExecution = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $lastReturnCode = null;

    /** Log's file name (without path). */
    #[ORM\Column(type: Types::STRING, length: 150, nullable: true)]
    private ?string $logFile = null;

    ##[Assert\Type(Integer::class)]
    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    private int $priority;

    /** If true, command will be execute next time regardless cron expression. */
    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    private bool $executeImmediately = false;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    private bool $disabled = false;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    private bool $locked = false;

    /**
     * Init new ScheduledCommand.
     */
    public function __construct()
    {
        $this->createdAt = new DateTime();
        #$this->setLastExecution(new DateTime());
        $this->lastExecution = null;
        $this->setLocked(false);
        $this->priority = 0;
        $this->version = 1;
    }

    #[Pure]
    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * Get id.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set id.
     *
     * @param int $id
     *
     * @return ScheduledCommand
     */
    public function setId(int $id): ScheduledCommand
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get name.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return ScheduledCommand
     */
    public function setName(string $name): ScheduledCommand
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get command.
     */
    public function getCommand(): ?string
    {
        return $this->command;
    }

    /**
     * Set command.
     *
     * @param string $command
     *
     * @return ScheduledCommand
     */
    public function setCommand(string $command): ScheduledCommand
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Get arguments.
     */
    public function getArguments(): ?string
    {
        return $this->arguments;
    }

    /**
     * Set arguments.
     *
     * @param string|null $arguments
     *
     * @return ScheduledCommand
     */
    public function setArguments(?string $arguments): ScheduledCommand
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * Get cronExpression.
     */
    public function getCronExpression(): ?string
    {
        return $this->cronExpression;
    }

    /**
     * Set cronExpression.
     *
     * @param string $cronExpression
     *
     * @return ScheduledCommand
     */
    public function setCronExpression(string $cronExpression): ScheduledCommand
    {
        $this->cronExpression = $cronExpression;

        return $this;
    }

    /**
     * Get lastExecution.
     */
    public function getLastExecution(): ?DateTime
    {
        return $this->lastExecution;
    }

    /**
     * Set lastExecution.
     *
     * @param DateTime $lastExecution
     *
     * @return ScheduledCommand
     */
    public function setLastExecution(DateTime $lastExecution): ScheduledCommand
    {
        $this->lastExecution = $lastExecution;

        return $this;
    }

    /**
     * Get logFile.
     */
    public function getLogFile(): ?string
    {
        return $this->logFile;
    }

    /**
     * Set logFile.
     *
     * @param string $logFile
     *
     * @return ScheduledCommand
     */
    public function setLogFile(string $logFile): ScheduledCommand
    {
        $this->logFile = $logFile;

        return $this;
    }

    /**
     * Get lastReturnCode.
     */
    public function getLastReturnCode(): ?int
    {
        return $this->lastReturnCode;
    }

    /**
     * Set lastReturnCode.
     *
     * @param int|null $lastReturnCode
     *
     * @return ScheduledCommand
     */
    public function setLastReturnCode(?int $lastReturnCode): ScheduledCommand
    {
        $this->lastReturnCode = $lastReturnCode;

        return $this;
    }

    /**
     * Get priority.
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Set priority.
     *
     * @param int $priority
     *
     * @return ScheduledCommand
     */
    public function setPriority(int $priority): ScheduledCommand
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get executeImmediately.
     */
    public function isExecuteImmediately(): bool
    {
        return $this->executeImmediately;
    }

    /**
     * Get executeImmediately.
     */
    public function getExecuteImmediately(): bool
    {
        return $this->executeImmediately;
    }

    /**
     * Set executeImmediately.
     *
     * @param bool $executeImmediately
     *
     * @return ScheduledCommand
     */
    public function setExecuteImmediately(bool $executeImmediately): ScheduledCommand
    {
        $this->executeImmediately = $executeImmediately;

        return $this;
    }

    /**
     * Get disabled.
     */
    public function isDisabled(): ?bool
    {
        return $this->disabled;
    }

    /**
     * Get disabled.
     */
    public function getDisabled(): ?bool
    {
        return $this->disabled;
    }

    /**
     * Set disabled.
     *
     * @param bool $disabled
     *
     * @return ScheduledCommand
     */
    public function setDisabled(bool $disabled): ScheduledCommand
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * Locked Getter.
     */
    public function isLocked(): ?bool
    {
        return $this->locked;
    }

    /**
     * locked Getter.
     */
    public function getLocked(): ?bool
    {
        return $this->locked;
    }

    /**
     * locked Setter.
     *
     * @param bool $locked
     *
     * @return ScheduledCommand
     */
    public function setLocked(bool $locked): ScheduledCommand
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * @return ?DateTime
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     * @return DateTime
     */
    public function setCreatedAt(DateTime $createdAt): DateTime
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Returns the next run time of the scheduled command
     * @param bool $checkExecuteImmediately Check if immediately execution is set
     *
     * @return DateTime|null
     *
     * @throws \Exception
     */
    public function getNextRunDate(bool $checkExecuteImmediately = true): ?DateTime
    {
        if($this->getDisabled() || $this->getLocked())
        {return null;}

        if ($checkExecuteImmediately && $this->getExecuteImmediately()) {
            return new DateTime();
        }

        return (new CronExpressionLib($this->getCronExpression()))->getNextRunDate();
    }

    /**
     * Get a human readable format of the next run of the scheduled command
     * @example 3 minutes
     * @return string|null
     */
    public function getNextRunDateForHumans(): ?string
    {
        try{
            return Carbon::instance($this->getNextRunDate())->diffForHumans();
        }
        catch (\Exception)
        {}

        return null;
    }
}
