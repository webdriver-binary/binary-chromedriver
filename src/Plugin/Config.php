<?php

namespace Lanfest\ChromeDriver\Plugin;

use Lanfest\WebDriverBinaryDownloader\Interfaces\PlatformAnalyserInterface as Platform;

class Config implements \Lanfest\WebDriverBinaryDownloader\Interfaces\ConfigInterface
{
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

    public function getPreferences()
    {
        $extra = $this->configOwner->getExtra();

        $defaults = array(
            'version' => null
        );

        return array_replace(
            $defaults,
            isset($extra['chromedriver']) ? $extra['chromedriver'] : array()
        );
    }

    public function getDriverName()
    {
        return 'ChromeDriver';
    }
    
    public function getRequestUrlConfig()
    {
        $baseUrl = 'https://chromedriver.storage.googleapis.com';
        
        return array(
            self::REQUEST_VERSION => array(
                sprintf('%s/LATEST_RELEASE_{{major}}', $baseUrl),
                sprintf('%s/LATEST_RELEASE', $baseUrl)
            ),
            self::REQUEST_DOWNLOAD => sprintf('%s/{{version}}/{{file}}', $baseUrl)
        );
    }
    
    public function getBrowserBinaryPaths()
    {
        return array(
            Platform::TYPE_LINUX32 => array(
                '/usr/bin/google-chrome'
            ),
            Platform::TYPE_LINUX64 => array(
                '/usr/bin/google-chrome'
            ),
            Platform::TYPE_MAC64 => array(
                '/Applications/Google\ Chrome.app/Contents/MacOS/Google\ Chrome'
            ),
            Platform::TYPE_WIN32 => array(
                'C:\\\\Program Files (x86)\\\\Google\\\\Chrome\\\\Application\\\\chrome.exe'
            ),
            Platform::TYPE_WIN64 => array(
                'C:\\\\Program Files (x86)\\\\Google\\\\Chrome\\\\Application\\\\chrome.exe'
            )
        );
    }
    
    public function getBrowserVersionPollingConfig()
    {
        return array(
            '%s -version' => array('Google Chrome ([0-9].+)'),
            'wmic datafile where name="%s" get Version /value' => array('Version=([0-9].+)')
        );
    }
    
    public function getDriverVersionPollingConfig()
    {
        return array(
            '%s --version' => array('ChromeDriver ([0-9].+) \(')
        );
    }
    
    public function getBrowserDriverVersionMap()
    {
        return array(
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
            '1' => '2.0'
        );
    }
    
    public function getRemoteFileNames()
    {
        return array(
            Platform::TYPE_LINUX32 => 'chromedriver_linux32.zip',
            Platform::TYPE_LINUX64 => 'chromedriver_linux64.zip',
            Platform::TYPE_MAC64 => 'chromedriver_mac64.zip',
            Platform::TYPE_WIN32 => 'chromedriver_win32.zip',
            Platform::TYPE_WIN64 => 'chromedriver_win32.zip'
        );
    }

    public function getExecutableFileNames()
    {
        return array(
            Platform::TYPE_LINUX32 => 'chromedriver',
            Platform::TYPE_LINUX64 => 'chromedriver',
            Platform::TYPE_MAC64 => 'chromedriver',
            Platform::TYPE_WIN32 => 'chromedriver.exe',
            Platform::TYPE_WIN64 => 'chromedriver.exe'
        );
    }

    public function getDriverVersionHashMap()
    {
        return array();
    }
    
    public function getExecutableFileRenames()
    {
        return array();
    }
}
