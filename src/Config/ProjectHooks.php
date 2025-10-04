<?php

declare(strict_types=1);

namespace HeyFrame\Deployment\Config;

class ProjectHooks
{
    public function __construct(
        public string $pre = '',
        public string $post = '',
        public string $preInstall = '',
        public string $postInstall = '',
        public string $preUpdate = '',
        public string $postUpdate = '',
    ) {
    }
}
