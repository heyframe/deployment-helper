<?php

declare(strict_types=1);

namespace HeyFrame\Deployment\Tests\Integration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use HeyFrame\Deployment\Config\ProjectConfiguration;
use HeyFrame\Deployment\Event\PostDeploy;
use HeyFrame\Deployment\Helper\ProcessHelper;
use HeyFrame\Deployment\Integration\ClearAlwaysCacheSubscriber;
use HeyFrame\Deployment\Struct\RunConfiguration;
use Symfony\Component\Console\Output\NullOutput;

#[CoversClass(ClearAlwaysCacheSubscriber::class)]
class ClearAlwaysCacheSubscriberTest extends TestCase
{
    private ProjectConfiguration&MockObject $projectConfiguration;
    private ProcessHelper&MockObject $processHelper;
    private ClearAlwaysCacheSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->projectConfiguration = $this->createMock(ProjectConfiguration::class);
        $this->processHelper = $this->createMock(ProcessHelper::class);
        $this->subscriber = new ClearAlwaysCacheSubscriber($this->projectConfiguration, $this->processHelper);
    }

    public function testInvokeWithAlwaysClearCacheEnabled(): void
    {
        $this->projectConfiguration->alwaysClearCache = true;

        $this->processHelper
            ->expects($this->once())
            ->method('console')
            ->with(['cache:pool:clear', 'cache.http', 'cache.object']);

        $event = new PostDeploy(new RunConfiguration(), new NullOutput());
        $this->subscriber->__invoke($event);
    }

    public function testInvokeWithAlwaysClearCacheDisabled(): void
    {
        $this->projectConfiguration->alwaysClearCache = false;

        $this->processHelper
            ->expects($this->never())
            ->method('console');

        $event = new PostDeploy(new RunConfiguration(), new NullOutput());
        $this->subscriber->__invoke($event);
    }
}
