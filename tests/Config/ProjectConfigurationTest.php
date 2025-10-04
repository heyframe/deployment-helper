<?php

declare(strict_types=1);

namespace HeyFrame\Deployment\Tests\Config;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use HeyFrame\Deployment\Config\ProjectConfiguration;
use HeyFrame\Deployment\Config\ProjectHooks;
use HeyFrame\Deployment\Config\ProjectMaintenance;
use HeyFrame\Deployment\Config\ProjectStore;

#[CoversClass(ProjectConfiguration::class)]
#[CoversClass(ProjectHooks::class)]
#[CoversClass(ProjectMaintenance::class)]
#[CoversClass(ProjectStore::class)]
class ProjectConfigurationTest extends TestCase
{
    public function testConstructor(): void
    {
        $config = new ProjectConfiguration();

        static::assertEmpty($config->hooks->pre);
        static::assertEmpty($config->hooks->post);
        static::assertEmpty($config->hooks->preInstall);
        static::assertEmpty($config->hooks->postInstall);
        static::assertEmpty($config->hooks->preUpdate);
        static::assertEmpty($config->hooks->postUpdate);

        static::assertTrue($config->extensionManagement->enabled);
        static::assertEmpty($config->extensionManagement->overrides);

        static::assertEmpty($config->store->licenseDomain);
    }
}
