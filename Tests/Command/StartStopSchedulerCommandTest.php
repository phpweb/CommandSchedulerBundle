<?php

namespace Dukecity\CommandSchedulerBundle\Tests\Command;

use Dukecity\CommandSchedulerBundle\Command\StartSchedulerCommand;
use Dukecity\CommandSchedulerBundle\Command\StopSchedulerCommand;
use Dukecity\CommandSchedulerBundle\Fixtures\ORM\LoadScheduledCommandData;

/**
 * Class StartStopSchedulerCommandTest.
 */
class StartStopSchedulerCommandTest extends AbstractCommandTest
{
    /**
     * Test scheduler:start and scheduler:stop.
     */
    public function testStartAndStopScheduler()
    {
        // DataFixtures create 4 records
        $this->loadScheduledCommandFixtures();

        $pidFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.StartSchedulerCommand::PID_FILE;

        $output = $this->executeCommand(StartSchedulerCommand::class)->getDisplay();
        $this->assertStringStartsWith('Command scheduler started in non-blocking mode...', $output);
        $this->assertFileExists($pidFile);

        $output = $this->executeCommand(StopSchedulerCommand::class)->getDisplay();
        $this->assertStringStartsWith('Command scheduler is stopped.', $output);

        # PHPUnit 9
        if(method_exists($this, "assertFileDoesNotExist"))
        {$this->assertFileDoesNotExist($pidFile);}

        # PHPUnit 8
        if(method_exists($this, "assertFileNotExists") &&
            (!method_exists($this, "assertFileDoesNotExist"))
        )
        {$this->assertFileNotExists($pidFile);}
    }
}
