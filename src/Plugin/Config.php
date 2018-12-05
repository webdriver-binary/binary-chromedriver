<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ChromeDriver\Plugin;

use Vaimo\ChromeDriver\Installer\PlatformAnalyser as Platform;

class Config
{
    const REQUEST_VERSION = 'base';
    const REQUEST_DOWNLOAD = 'base';
    
    /**
     * @var \Composer\Package\PackageInterface
     */
    private $configOwner;

    /**
     * @param \Composer\Package\PackageInterface $configOwner
     */
    public function __construct(
        \Composer\Package\PackageInterface $configOwner
    ) {
        $this->configOwner = $configOwner;
    }
    
    public function getRequestUrlConfig()
    {
        $baseUrl = 'https://chromedriver.storage.googleapis.com';
        
        return [
            self::REQUEST_VERSION => sprintf('%s/LATEST_RELEASE', $baseUrl),
            self::REQUEST_DOWNLOAD => sprintf('%s/{{version}}/{{file}}', $baseUrl)
        ];
    }
    
    public function getPreferences()
    {
        $extra = $this->configOwner->getExtra();

        $defaults = [
            'version' => null
        ];

        $config = array_replace(
            $defaults,
            isset($extra['chromedriver']) ? $extra['chromedriver'] : []
        );

        if (isset($config['chromedriver-version'])) {
            $config['version'] = $config['chromedriver-version'];
        }

        return $config;
    }
    
    public function getBrowserBinaryPaths()
    {
        return [
            Platform::TYPE_LINUX32 => [
                '/usr/bin/google-chrome'
            ],
            Platform::TYPE_LINUX64 => [
                '/usr/bin/google-chrome'
            ],
            Platform::TYPE_MAC64 => [
                '/Applications/Google\ Chrome.app/Contents/MacOS/Google\ Chrome'
            ]
        ];
    }
    
    public function getBrowserVersionPollingConfig()
    {
        return [
            '%s -version' => ['Google Chrome %s']  
        ];
    }

    public function getDriverName()
    {
        return 'ChromeDriver';
    }
    
    public function getDriverVersionPollingConfig()
    {
        return [
            '%s --version' => ['ChromeDriver %s (']
        ];
    }
    
    public function getBrowserDriverVersionMap()
    {
        return [
            '72' => '',
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
            '42' => '2.15'
        ];
    }
    
    public function getRemoteFileNames()
    {
        return [
            Platform::TYPE_LINUX32 => 'chromedriver_linux32.zip',
            Platform::TYPE_LINUX64 => 'chromedriver_linux64.zip',
            Platform::TYPE_MAC64 => 'chromedriver_mac64.zip',
            Platform::TYPE_WIN32 => 'chromedriver_win32.zip',
            Platform::TYPE_WIN64 => 'chromedriver_win32.zip'
        ];
    }

    public function getExecutableFileNames()
    {
        return [
            Platform::TYPE_LINUX32 => 'chromedriver',
            Platform::TYPE_LINUX64 => 'chromedriver',
            Platform::TYPE_MAC64 => 'chromedriver',
            Platform::TYPE_WIN32 => 'chromedriver.exe',
            Platform::TYPE_WIN64 => 'chromedriver.exe'
        ];
    }
}
