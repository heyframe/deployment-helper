<?php

declare(strict_types=1);

namespace HeyFrame\Deployment\Tests\Command;

use HeyFrame\Deployment\Command\OneTimeTaskMarkCommand;
use HeyFrame\Deployment\Services\OneTimeTasks;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(OneTimeTaskMarkCommand::class)]
class OneTimeTaskMarkCommandTest extends TestCase
{
    public function testMark(): void
    {
        $taskService = $this->createMock(OneTimeTasks::class);
        $taskService
            ->expects($this->once())
            ->method('markAsRun')
            ->with('test');

        $cmd = new OneTimeTaskMarkCommand($taskService);
        $tester = new CommandTester($cmd);
        $tester->execute(['id' => 'test']);

        $tester->assertCommandIsSuccessful();
    }

    public function testMarkAgain(): void
    {
        $taskService = $this->createMock(OneTimeTasks::class);
        $taskService
            ->expects($this->once())
            ->method('markAsRun')
            ->willThrowException(new \Exception('Task already marked as run'));

        $cmd = new OneTimeTaskMarkCommand($taskService);

        $tester = new CommandTester($cmd);
        $tester->execute(['id' => 'test']);

        static::assertSame(Command::FAILURE, $tester->getStatusCode());
    }
}
