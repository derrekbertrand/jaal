<?php

namespace DialInno\Jaal\Tests;

use DialInno\Jaal\Tests\TestCase;
use DialInno\Jaal\Tests\Api\BadApi;
use DialInno\Jaal\Commands\ApiMakeCommand;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Tester\CommandTester;


class JaalMakeCommandTest extends TestCase
{
    protected $business;

    public function setUp()
    {
        parent::setUp();
        //consider moving this into TestCase to be useful for multiple command test classes

        $application = new ConsoleApplication();

        $makeCommand = $this->app->make(ApiMakeCommand::class);
        $makeCommand->setLaravel(app());

        $application->add($makeCommand);

        $this->command = $application->find('jaal:make');

        $this->commandTester = new CommandTester($this->command);

    }

    /** @test */
    public function exception_is_thrown_if_required_arguments_are_missing()
    {
    	$this->expectException(\RunTimeException::class);

        $this->commandTester->execute([
            'command' => $this->command->getName(),
        ]);
    }

}
