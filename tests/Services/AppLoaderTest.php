<?php

declare(strict_types=1);

namespace HeyFrame\Deployment\Tests\Services;

use Composer\InstalledVersions;
use HeyFrame\Deployment\Services\AppLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AppLoader::class)]
class AppLoaderTest extends TestCase
{
    public function testNotExistingAppsFolder(): void
    {
        $appLoader = new AppLoader(__DIR__);

        static::assertSame([], $appLoader->all());
    }

    public function testLocalApp(): void
    {
        $appLoader = new AppLoader(__DIR__ . '/_fixtures/correct');

        static::assertSame([
            ['name' => 'TestApp', 'version' => '1.0.1'],
        ], $appLoader->all());
    }

    public function testLocalAppInvalidApp(): void
    {
        $appLoader = new AppLoader(__DIR__ . '/_fixtures/invalid');

        static::assertSame([], $appLoader->all());
    }

    public function testLoadFromComposer(): void
    {
        $before = InstalledVersions::getAllRawData()[0];

        InstalledVersions::reload([
            'root' => $before['root'],
            'versions' => [
                'foo/foo' => [
                    'name' => 'foo/foo',
                    'version' => '1.0.0',
                    'type' => 'heyframe-app',
                    'install_path' => __DIR__ . '/_fixtures/correct/custom/apps/TestApp',
                    'dev_requirement' => false,
                ],
            ],
        ]);

        $appLoader = new AppLoader(__DIR__);

        static::assertSame([
            ['name' => 'TestApp', 'version' => '1.0.1'],
        ], $appLoader->all());

        InstalledVersions::reload($before);
    }
}
