<?php

declare(strict_types=1);

namespace HeyFrame\Deployment\Services;

use Composer\InstalledVersions;
use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Finder\Finder;

/**
 * @phpstan-type App array{name: string, version: string}
 */
class AppLoader
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    /**
     * @return list<App>
     */
    public function all(): array
    {
        $files = [...$this->loadFromFilesystem(), ...$this->loadFromComposer()];

        return $this->loadApps($files);
    }

    /**
     * @return list<string>
     */
    private function loadFromFilesystem(): array
    {
        $appDir = $this->projectDir . '/custom/apps';
        if (!file_exists($appDir)) {
            return [];
        }

        $files = [];
        $finder = new Finder();
        $finder->in($appDir)
            ->depth('<= 1')
            ->name('manifest.xml')
        ;

        foreach ($finder->files() as $xml) {
            $files[] = $xml->getPathname();
        }

        return $files;
    }

    /**
     * @return list<string>
     */
    private function loadFromComposer(): array
    {
        $files = [];

        foreach (InstalledVersions::getInstalledPackagesByType('heyframe-app') as $packageName) {
            $path = InstalledVersions::getInstallPath($packageName);

            $files[] = $path . '/manifest.xml';
        }

        return $files;
    }

    /**
     * @param list<string> $files
     *
     * @return list<App>
     */
    private function loadApps(array $files): array
    {
        $apps = [];

        foreach ($files as $file) {
            $appXml = XmlUtils::loadFile($file);

            $xpath = new \DOMXPath($appXml);

            $name = $this->getNodeValueByPath($xpath, '/manifest/meta/name');
            $version = $this->getNodeValueByPath($xpath, '/manifest/meta/version');

            if ($name === null || $version === null) {
                continue;
            }

            $apps[] = [
                'name' => $name,
                'version' => $version,
            ];
        }

        return $apps;
    }

    private function getNodeValueByPath(\DOMXPath $xpath, string $query): ?string
    {
        $node = $xpath->query($query);
        \assert($node instanceof \DOMNodeList);

        return $node->item(0)?->nodeValue;
    }
}
