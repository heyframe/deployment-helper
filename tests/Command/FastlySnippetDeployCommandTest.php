<?php

declare(strict_types=1);

namespace HeyFrame\Deployment\Tests\Command;

use HeyFrame\Deployment\Command\FastlySnippetDeployCommand;
use HeyFrame\Deployment\Integration\Fastly\FastlyServiceUpdater;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Zalas\PHPUnit\Globals\Attribute\Env;

#[CoversClass(FastlySnippetDeployCommand::class)]
class FastlySnippetDeployCommandTest extends TestCase
{
    public function testRunCommandWithoutEnv(): void
    {
        $updater = $this->createMock(FastlyServiceUpdater::class);
        $updater
            ->expects($this->never())
            ->method('__invoke');

        $command = new FastlySnippetDeployCommand($updater);
        $tester = new CommandTester($command);

        $tester->execute([]);

        static::assertEquals(Command::FAILURE, $tester->getStatusCode());
        static::assertStringContainsString('FASTLY_API_TOKEN or FASTLY_SERVICE_ID is not set.', $tester->getDisplay());
    }

    #[Env('FASTLY_API_TOKEN', 'apiToken')]
    #[Env('FASTLY_SERVICE_ID', 'serviceId')]
    public function testRunCommandWithEnv(): void
    {
        $updater = $this->createMock(FastlyServiceUpdater::class);
        $updater
            ->expects($this->once())
            ->method('__invoke');

        $command = new FastlySnippetDeployCommand($updater);
        $tester = new CommandTester($command);

        $tester->execute([]);

        static::assertEquals(Command::SUCCESS, $tester->getStatusCode());
    }
}
