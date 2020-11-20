<?php

declare(strict_types=1);

namespace Lanfest\ChromeDriver\Plugin;

use Lanfest\WebDriverBinaryDownloader\Interfaces\PlatformAnalyserInterface as Platform;

class Config implements \Lanfest\WebDriverBinaryDownloader\Interfaces\ConfigInterface
{
    /** @var \Composer\Package\PackageInterface */
    private $configOwner;

    public function __construct(
        \Composer\Package\PackageInterface $configOwner
    ) {
        $this->configOwner = $configOwner;
    }

    public function getPreferences(): array
    {
        $extra = $this->configOwner->getExtra();

        $defaults = [
            'version' => null,
        ];

        return array_replace(
            $defaults,
            $extra['chromedriver'] ?? []
        );
    }

    public function getDriverName(): string
    {
        return 'ChromeDriver';
    }

    public function getRequestUrlConfig(): array
    {
        $baseUrl = 'https://chromedriver.storage.googleapis.com';

        return [
            self::REQUEST_VERSION => [
                sprintf('%s/LATEST_RELEASE_{{major}}', $baseUrl),
                sprintf('%s/LATEST_RELEASE', $baseUrl),
            ],
            self::REQUEST_DOWNLOAD => sprintf('%s/{{version}}/{{file}}', $baseUrl),
        ];
    }

    public function getBrowserBinaryPaths(): array
    {
        return [
            Platform::TYPE_LINUX32 => [
                '/usr/bin/google-chrome',
            ],
            Platform::TYPE_LINUX64 => [
                '/usr/bin/google-chrome',
            ],
            Platform::TYPE_MAC64 => [
                '/Applications/Google\ Chrome.app/Contents/MacOS/Google\ Chrome',
            ],
            Platform::TYPE_WIN32 => [
                'C:\\\\Program Files (x86)\\\\Google\\\\Chrome\\\\Application\\\\chrome.exe',
            ],
            Platform::TYPE_WIN64 => [
                'C:\\\\Program Files (x86)\\\\Google\\\\Chrome\\\\Application\\\\chrome.exe',
            ],
        ];
    }

    public function getBrowserVersionPollingConfig(): array
    {
        return [
            '%s -version' => ['Google Chrome ([0-9].+)'],
            'wmic datafile where name="%s" get Version /value' => ['Version=([0-9].+)'],
        ];
    }

    public function getDriverVersionPollingConfig(): array
    {
        return [
            '%s --version' => ['ChromeDriver ([0-9].+) \('],
        ];
    }

    public function getBrowserDriverVersionMap(): array
    {
        return [
            '78' => '',
            '77' => '77.0.3865.40',
            '76' => '76.0.3809.126',
            '75' => '75.0.3770.140',
            '74' => '74.0.3729.6',
            '73' => '73.0.3683.68',
            '71' => '2.46',
            '70' => '2.45',
            '69' => '2.44',
            '68' => '2.42',
            '67' => '2.41',
            '66' => '2.40',
            '65' => '2.38',
            '64' => '2.37',
            '63' => '2.36',
            '62' => '2.35',
            '61' => '2.34',
            '60' => '2.33',
            '57' => '2.28',
            '54' => '2.25',
            '53' => '2.24',
            '51' => '2.22',
            '44' => '2.19',
            '42' => '2.15',
            '1' => '2.0',
        ];
    }

    public function getRemoteFileNames(): array
    {
        return [
            Platform::TYPE_LINUX32 => 'chromedriver_linux32.zip',
            Platform::TYPE_LINUX64 => 'chromedriver_linux64.zip',
            Platform::TYPE_MAC64 => 'chromedriver_mac64.zip',
            Platform::TYPE_WIN32 => 'chromedriver_win32.zip',
            Platform::TYPE_WIN64 => 'chromedriver_win32.zip',
        ];
    }

    public function getExecutableFileNames(): array
    {
        return [
            Platform::TYPE_LINUX32 => 'chromedriver',
            Platform::TYPE_LINUX64 => 'chromedriver',
            Platform::TYPE_MAC64 => 'chromedriver',
            Platform::TYPE_WIN32 => 'chromedriver.exe',
            Platform::TYPE_WIN64 => 'chromedriver.exe',
        ];
    }

    public function getDriverVersionHashMap(): array
    {
        return [];
    }

    public function getExecutableFileRenames(): array
    {
        return [];
    }
}
