<?php

declare(strict_types=1);

namespace HeyFrame\Deployment\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use HeyFrame\Deployment\Application;
use HeyFrame\Deployment\Command\RunCommand;
use Zalas\PHPUnit\Globals\Attribute\Env;

#[CoversClass(Application::class)]
class ApplicationTest extends TestCase
{
    #[Env('PROJECT_ROOT', __DIR__ . '/..')]
    public function testCanBoot(): void
    {
        $app = new Application();
        static::assertTrue($app->getContainer()->has(RunCommand::class));
    }

    #[Env('PROJECT_ROOT', __DIR__ . '/..')]
    #[Env('DEV_MODE', '1')]
    public function testWithDevMode(): void
    {
        $app = new Application();
        static::assertTrue($app->getContainer()->has(RunCommand::class));
        static::assertFileExists(\dirname(__DIR__) . '/var/cache/container.xml');
    }
}
