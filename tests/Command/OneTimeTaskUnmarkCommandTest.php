<?php

declare(strict_types=1);

namespace HeyFrame\Deployment\Tests\Command;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use HeyFrame\Deployment\Command\OneTimeTaskUnmarkCommand;
use HeyFrame\Deployment\Services\OneTimeTasks;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(OneTimeTaskUnmarkCommand::class)]
class OneTimeTaskUnmarkCommandTest extends TestCase
{
    public function testUnmark(): void
    {
        $taskService = $this->createMock(OneTimeTasks::class);
        $taskService
            ->expects($this->once())
            ->method('remove')
            ->with('test');
        $taskService
            ->expects($this->once())
            ->method('getExecutedTasks')
            ->willReturn(['test' => ['created_at' => '2023-10-01 00:00:00']]);

        $cmd = new OneTimeTaskUnmarkCommand($taskService);
        $tester = new CommandTester($cmd);
        $tester->execute(['id' => 'test']);

        $tester->assertCommandIsSuccessful();
    }

    public function testUnmarkMissing(): void
    {
        $taskService = $this->createMock(OneTimeTasks::class);
        $taskService
            ->expects($this->once())
            ->method('getExecutedTasks')
            ->willReturn([]);

        $cmd = new OneTimeTaskUnmarkCommand($taskService);
        $tester = new CommandTester($cmd);
        $tester->execute(['id' => 'test']);

        Assert::assertSame($tester->getStatusCode(), Command::FAILURE);
    }
}
