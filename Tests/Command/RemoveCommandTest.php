<?php

namespace Dukecity\CommandSchedulerBundle\Tests\Command;

use Dukecity\CommandSchedulerBundle\Command\RemoveCommand;
use Dukecity\CommandSchedulerBundle\Entity\ScheduledCommand;
use Dukecity\CommandSchedulerBundle\Fixtures\ORM\LoadScheduledCommandData;

/**
 * Class RemoveCommandTest.
 */
class RemoveCommandTest extends AbstractCommandTest
{
    /**
     * Test scheduler:remove with given command name.
     */
    public function testRemove()
    {
        // DataFixtures create 4 records
        $this->loadScheduledCommandFixtures();

        // remove a command that does not exist
        $output = $this->executeCommand(RemoveCommand::class, ['name' => 'abc'], [], 1)->getDisplay();
        $this->assertStringContainsString('Could not', $output);

        // empty command-name
        $output = $this->executeCommand(RemoveCommand::class, ['name' => ''], [], 1)->getDisplay();
        $this->assertStringContainsString('Could not', $output);

        // Remove command
        $output = $this->executeCommand(RemoveCommand::class, ['name' => 'CommandTestTwo'])->getDisplay();
        $this->assertStringContainsString('successfully', $output);

        // Not in DB anymore
        $two = $this->em->getRepository(ScheduledCommand::class)->findOneBy(['name' => 'CommandTestTwo']);
        $this->assertNull($two);

        // Fails now
        $output = $this->executeCommand(RemoveCommand::class, ['name' => 'CommandTestTwo'], [], 1)->getDisplay();
        $this->assertStringContainsString('Could not', $output);
    }
}
