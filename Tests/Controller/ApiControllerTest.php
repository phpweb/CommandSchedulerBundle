<?php

namespace Dukecity\CommandSchedulerBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Dukecity\CommandSchedulerBundle\Entity\ScheduledCommand;
use Dukecity\CommandSchedulerBundle\Fixtures\ORM\LoadScheduledCommandData;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiControllerTest.
 */
class ApiControllerTest extends WebTestCase
{
    protected AbstractDatabaseTool $databaseTool;
    private KernelBrowser $client;
    private EntityManager $em;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->client = self::createClient();
        $this->client->followRedirects(true);
        
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->databaseTool = $this->client->getContainer()->get(DatabaseToolCollection::class)->get();
    }

    /**
     * Test list all command URL with should return json.
     */
    public function testConsoleCommands()
    {
        // List all available console commands
        $this->client->request('GET', '/command-scheduler/api/console_commands');
        $this->assertResponseIsSuccessful();

        $jsonResponse = $this->client->getResponse()->getContent();
        $jsonArray = json_decode($jsonResponse, true);

        $this->assertGreaterThanOrEqual(1, count($jsonArray));
        $this->assertArrayHasKey('_global', $jsonArray);
        $this->assertSame("assets:install", $jsonArray["assets"]["assets:install"]);
        $this->assertSame("debug:autowiring", $jsonArray["debug"]["debug:autowiring"]);
    }

    /**
     * Test list all command URL with should return json.
     */
    public function testConsoleCommandsDetailsAll()
    {
        // List all available console commands
        $this->client->request('GET', '/command-scheduler/api/console_commands_details');
        $this->assertResponseIsSuccessful();

        $jsonResponse = $this->client->getResponse()->getContent();
        $commands = json_decode($jsonResponse, true);

        $this->assertIsArray($commands);
        $this->assertArrayHasKey('about', $commands);
        $this->assertSame("about", $commands["about"]["name"]);

        $this->assertArrayHasKey('list', $commands);
        $this->assertArrayHasKey('cache:clear', $commands);
    }

    /**
     * Test list all command URL with should return json.
     */
    public function testConsoleCommandsDetails()
    {
        // List all available console commands
        $this->client->request('GET', '/command-scheduler/api/console_commands_details/about,list,cache:clear,asserts:install');
        $this->assertResponseIsSuccessful();

        $jsonResponse = $this->client->getResponse()->getContent();
        $commands = json_decode($jsonResponse, true);

        $this->assertIsArray($commands);
        $this->assertArrayHasKey('about', $commands);
        $this->assertSame("about", $commands["about"]["name"]);

        $this->assertArrayHasKey('list', $commands);
        $this->assertArrayHasKey('cache:clear', $commands);
    }

    /**
     * Test list all command URL with should return json.
     */
    public function testList()
    {
        // DataFixtures create 4 records
        $this->databaseTool->loadFixtures([LoadScheduledCommandData::class]);

        // List 4 Commands
        $this->client->request('GET', '/command-scheduler/api/list');
        $this->assertResponseIsSuccessful();

        $jsonResponse = $this->client->getResponse()->getContent();
        $jsonArray = json_decode($jsonResponse, true);
        $this->assertEquals(5, count($jsonArray));
        $this->assertSame('CommandTestOne', $jsonArray['CommandTestOne']['NAME']);
    }

    /**
     * Test monitoring URL with json.
     */
    public function testMonitorWithErrors()
    {
        // DataFixtures create 4 records
        $this->databaseTool->loadFixtures([LoadScheduledCommandData::class]);

        // One command is locked in fixture (2), another have a -1 return code as lastReturn (4)
        $this->client->request('GET', '/command-scheduler/monitor');
        $this->assertResponseStatusCodeSame(Response::HTTP_EXPECTATION_FAILED);

        // We expect 2 commands
        $jsonResponse = $this->client->getResponse()->getContent();
        $jsonArray = json_decode($jsonResponse, true);
        $this->assertEquals(2, count($jsonArray));
    }

    /**
     * Test monitoring URL with json.
     */
    public function testMonitorWithoutErrors()
    {
        // DataFixtures create 4 records
        $this->databaseTool->loadFixtures([LoadScheduledCommandData::class]);

        $two = $this->em->getRepository(ScheduledCommand::class)->find(2);
        $four = $this->em->getRepository(ScheduledCommand::class)->find(4);
        $two->setLocked(false);
        $four->setLastReturnCode(0);

        try {
            $this->em->flush();
        } catch (OptimisticLockException | ORMException $e) {
        }

        // One command is locked in fixture (2), another have a -1 return code as lastReturn (4)
        $this->client->request('GET', '/command-scheduler/monitor');
        $this->assertResponseIsSuccessful();

        $jsonResponse = $this->client->getResponse()->getContent();
        $jsonArray = json_decode($jsonResponse, true);
        $this->assertCount(0, $jsonArray);
    }

    /**
     * Test translations
     */
    public function testTranslateCronExpression()
    {
        $this->client->request('GET', '/command-scheduler/api/trans_cron_expression/* * * * */en');
        $this->assertResponseIsSuccessful();

        $jsonResponse = $this->client->getResponse()->getContent();
        $jsonArray = json_decode($jsonResponse, true);

        $this->assertSame(0, $jsonArray["status"]);
        $this->assertSame("Every minute", $jsonArray["message"]);
    }
}