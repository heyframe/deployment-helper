<?php

declare(strict_types=1);

namespace HeyFrame\Deployment\Struct;

readonly class RunConfiguration
{
    public function __construct(
        public bool $skipThemeCompile = false,
        public bool $skipAssetsInstall = false,
        public ?float $timeout = 60,
        public bool $forceReinstallation = false,
    ) {
    }
}
