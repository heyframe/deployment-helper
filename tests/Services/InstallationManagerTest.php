<?php

declare(strict_types=1);

namespace HeyFrame\Deployment\Tests\Services;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use HeyFrame\Deployment\Config\ProjectConfiguration;
use HeyFrame\Deployment\Helper\ProcessHelper;
use HeyFrame\Deployment\Services\AccountService;
use HeyFrame\Deployment\Services\AppHelper;
use HeyFrame\Deployment\Services\HookExecutor;
use HeyFrame\Deployment\Services\InstallationManager;
use HeyFrame\Deployment\Services\PluginHelper;
use HeyFrame\Deployment\Services\HeyFrameState;
use HeyFrame\Deployment\Struct\RunConfiguration;
use Symfony\Component\Console\Output\OutputInterface;
use Zalas\PHPUnit\Globals\Attribute\Env;

#[CoversClass(InstallationManager::class)]
#[Env('APP_URL', 'http://localhost')]
class InstallationManagerTest extends TestCase
{
    public function testRun(): void
    {
        $hookExecutor = $this->createMock(HookExecutor::class);
        $hookExecutor
            ->expects($this->exactly(2))
            ->method('execute');

        $manager = new InstallationManager(
            $this->createMock(HeyFrameState::class),
            $this->createMock(Connection::class),
            $this->createMock(ProcessHelper::class),
            $this->createMock(PluginHelper::class),
            $this->createMock(AppHelper::class),
            $hookExecutor,
            new ProjectConfiguration(),
            $this->createMock(AccountService::class),
        );

        $manager->run(new RunConfiguration(), $this->createMock(OutputInterface::class));
    }

    public function testRunNoStorefront(): void
    {
        $state = $this->createMock(HeyFrameState::class);
        $state->method('isStorefrontInstalled')
            ->willReturn(true);

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->once())
            ->method('executeStatement')
            ->with('DELETE FROM sales_channel WHERE type_id = 0xf183ee5650cf4bdb8a774337575067a6');

        $manager = new InstallationManager(
            $state,
            $connection,
            $this->createMock(ProcessHelper::class),
            $this->createMock(PluginHelper::class),
            $this->createMock(AppHelper::class),
            $this->createMock(HookExecutor::class),
            new ProjectConfiguration(),
            $this->createMock(AccountService::class),
        );

        $manager->run(new RunConfiguration(), $this->createMock(OutputInterface::class));
    }

    public function testRunDisabledAssetCopyAndThemeCompile(): void
    {
        $state = $this->createMock(HeyFrameState::class);
        $state->method('isStorefrontInstalled')
            ->willReturn(true);

        $processHelper = $this->createMock(ProcessHelper::class);
        $consoleCommands = [];

        $processHelper
            ->method('console')
            ->willReturnCallback(function (array $command) use (&$consoleCommands): void {
                $consoleCommands[] = $command;
            });

        $accountService = $this->createMock(AccountService::class);
        $accountService->expects(static::never())->method('refresh');

        $manager = new InstallationManager(
            $state,
            $this->createMock(Connection::class),
            $processHelper,
            $this->createMock(PluginHelper::class),
            $this->createMock(AppHelper::class),
            $this->createMock(HookExecutor::class),
            new ProjectConfiguration(),
            $accountService,
        );

        $manager->run(new RunConfiguration(true, true), $this->createMock(OutputInterface::class));

        static::assertCount(7, $consoleCommands);
        static::assertSame(['system:install', '--create-database', '--shop-locale=en-GB', '--shop-currency=EUR', '--force', '--no-assign-theme', '--skip-assets-install'], $consoleCommands[0]);
    }

    public function testRunWithLicenseDomain(): void
    {
        $hookExecutor = $this->createMock(HookExecutor::class);
        $hookExecutor
            ->expects($this->exactly(2))
            ->method('execute');

        $configuration = new ProjectConfiguration();
        $configuration->store->licenseDomain = 'example.com';

        $accountService = $this->createMock(AccountService::class);
        $accountService->expects($this->once())->method('refresh');

        $manager = new InstallationManager(
            $this->createMock(HeyFrameState::class),
            $this->createMock(Connection::class),
            $this->createMock(ProcessHelper::class),
            $this->createMock(PluginHelper::class),
            $this->createMock(AppHelper::class),
            $hookExecutor,
            $configuration,
            $accountService,
        );

        $manager->run(new RunConfiguration(), $this->createMock(OutputInterface::class));
    }

    public function testRunWithForceReinstall(): void
    {
        $processHelper = $this->createMock(ProcessHelper::class);
        $consoleCommands = [];

        $processHelper
            ->method('console')
            ->willReturnCallback(function (array $command) use (&$consoleCommands): void {
                $consoleCommands[] = $command;
            });

        $accountService = $this->createMock(AccountService::class);
        $accountService->expects(static::never())->method('refresh');

        $manager = new InstallationManager(
            $this->createMock(HeyFrameState::class),
            $this->createMock(Connection::class),
            $processHelper,
            $this->createMock(PluginHelper::class),
            $this->createMock(AppHelper::class),
            $this->createMock(HookExecutor::class),
            new ProjectConfiguration(),
            $accountService,
        );

        $manager->run(new RunConfiguration(true, true, forceReinstallation: true), $this->createMock(OutputInterface::class));

        static::assertCount(4, $consoleCommands);
        static::assertSame(['system:install', '--create-database', '--shop-locale=en-GB', '--shop-currency=EUR', '--force', '--no-assign-theme', '--skip-assets-install', '--drop-database'], $consoleCommands[0]);
    }
}
