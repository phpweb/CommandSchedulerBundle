<?php

namespace Dukecity\CommandSchedulerBundle\Tests\Command;

use Dukecity\CommandSchedulerBundle\Command\AddCommand;
use Dukecity\CommandSchedulerBundle\Entity\ScheduledCommand;
use Dukecity\CommandSchedulerBundle\Fixtures\ORM\LoadScheduledCommandData;
use InvalidArgumentException;
use RuntimeException;

/**
 * Class AddCommandTest.
 */
class AddCommandTest extends AbstractCommandTest
{
    private array $testCommand = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->testCommand = [
            'name' => 'myCommand',
            'cmd' => 'debug:router',
            'arguments' => '',
            'cronExpression' => '@daily',
            'priority' => 10,
            'logFile' => 'mycommand.log',
            'executeImmediately' => false,
            'disabled' => false,
        ];
    }

    /**
     * Check
     */
    public function testDuplicateAdd()
    {
        // DataFixtures create 4 records
        $this->loadScheduledCommandFixtures();

        // Add command
        $output = $this->executeCommand(AddCommand::class, $this->testCommand)->getDisplay();
        $this->assertStringContainsString('successfully', $output);

        // Check if in DB
        $cmd_check = $this->em->getRepository(ScheduledCommand::class)->findOneBy(['name' => $this->testCommand["name"]]);
        self::assertIsScalar($this->testCommand["priority"], $cmd_check->getPriority());
        //$this->assertInstanceOf($cmd_check, ScheduledCommand);

        // Fails now
        $output = $this->executeCommand(AddCommand::class, $this->testCommand, [], 1)->getDisplay();
        $this->assertStringContainsString('Could not', $output);

        //
        $output = $this->executeCommand(AddCommand::class, [
            'name' => 'myCommand',
            'cmd' => 'debug:router',
            'arguments' => '',
            'cronExpression' => '@daily',
        ], [], 1)->getDisplay();
        $this->assertStringContainsString('Could not', $output);
    }

    /**
     * Test scheduler:add with given command name.
     *
     * @dataProvider getValidValues
     * @param array $command
     */
    public function testAdd(array $command)
    {
        // DataFixtures create 4 records
        $this->loadScheduledCommandFixtures();

        // Add command
        $output = $this->executeCommand(AddCommand::class, $command)->getDisplay();
        $this->assertStringContainsString('successfully', $output);

        // Check if in DB
        $cmd_check = $this->em->getRepository(ScheduledCommand::class)->findOneBy(['name' => $command["name"]]);
        self::assertIsScalar($command["cmd"], $cmd_check->getCommand());
        $this->assertInstanceOf(ScheduledCommand::class, $cmd_check);
    }


    /**
     * @return array
     */
    public function getValidValues(): array
    {
        return [
            'command1' => ["command" => [
                'name' => 'myCommand',
                'cmd' => 'debug:router',
                'arguments' => '',
                'cronExpression' => '@daily',
                'priority' => 10,
                'logFile' => 'mycommand.log',
                'executeImmediately' => false,
                'disabled' => false,
            ]],
            'command2' => ["command" => [
                'name' => '',
                'cmd' => 'debug:router',
                'arguments' => 'env="test"',
                'cronExpression' => '@daily',
                'priority' => -40,
                'logFile' => '',
                'executeImmediately' => true,
                'disabled' => true,
            ]],
            'minimumParameters' => ["command" => [
                'name' => 'myCommand',
                'cmd' => 'debug:router',
                'arguments' => '',
                'cronExpression' => '@daily',
            ]]
        ];
    }



    public function testInvalidArguments()
    {
        $command = $this->testCommand;
        $command['xxxx'] = 'avc';
        $this->expectException(InvalidArgumentException::class);
        $this->executeCommand(AddCommand::class, $command)->getDisplay();
    }

    /**
     * @dataProvider getInvalidRuntimeValues
     * @param array $command
     */
    public function testInvalidRuntimeValues(array $command)
    {
        $this->expectException(RuntimeException::class);
        $this->executeCommand(AddCommand::class, $command)->getDisplay();
    }


    public function getInvalidRuntimeValues(): array
    {
        return [
            'requiredParameterMissing1' => ["command" => [
                'name' => 'myCommand',
            ]],
            'requiredParameterMissing3' => ["command" => [
                'name' => 'myCommand',
                'cmd' => 'debug:router',
                'arguments' => '',
            ]]
        ];
    }


    /**
     * @dataProvider getInvalidValues
     * @param array $command
     */
    public function testInvalidValues(array $command)
    {
        $output = $this->executeCommand(AddCommand::class, $command, [], 1)->getDisplay();
        # Could not add the command
        $this->assertStringNotContainsString('successfully', $output);
    }

    public function getInvalidValues(): array
    {
        return [
            'cmdNotAvailable' => ["command" => [
                'name' => 'myCommand',
                'cmd' => 'debug:rout',
                'arguments' => '',
                'cronExpression' => '@daily',
                'priority' => 10,
                'logFile' => 'mycommand.log',
                'executeImmediately' => false,
                'disabled' => false,
            ]],
            'wrongDatatypPriority' => ["command" => [
                'name' => 'myCommand',
                'cmd' => 'debug:rout',
                'arguments' => '',
                'cronExpression' => '@daily',
                'priority' => "a2",
            ]],
            'wrongCronExpression' => ["command" => [
                'name' => 'myCommand',
                'cmd' => 'debug:rout',
                'arguments' => '',
                'cronExpression' => 'ABC',
            ]],
        ];
    }
}
