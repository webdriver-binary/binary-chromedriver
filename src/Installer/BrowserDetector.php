<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ChromeDriver\Installer;

class BrowserDetector
{
    /**
     * @var \Vaimo\ChromeDriver\Plugin\Config
     */
    private $pluginConfig;
    
    /**
     * @var \Vaimo\ChromeDriver\Utils\OsDetector
     */
    private $osDetector;

    /**
     * @var \Vaimo\ChromeDriver\Installer\Utils
     */
    private $installedUtils;

    /**
     * @var \Vaimo\ChromeDriver\Installer\VersionResolver
     */
    private $versionResolver;

    /**
     * @param \Vaimo\ChromeDriver\Plugin\Config $pluginConfig
     */
    public function __construct(
        \Vaimo\ChromeDriver\Plugin\Config $pluginConfig
    ) {
        $this->pluginConfig = $pluginConfig;
        
        $this->osDetector = new \Vaimo\ChromeDriver\Utils\OsDetector();
        $this->installedUtils = new \Vaimo\ChromeDriver\Installer\Utils();
        $this->versionResolver = new \Vaimo\ChromeDriver\Installer\VersionResolver();
    }
    
    public function resolveBrowserVersion()
    {
        $binaryPaths = $this->pluginConfig->getBrowserBinaryPaths();
        $platformCode = $this->osDetector->getPlatformCode();
        
        if (!isset($binaryPaths[$platformCode])) {
            return '';
        }
        
        return $this->versionResolver->pollForVersion(
            $binaryPaths[$platformCode], 
            $this->pluginConfig->getBrowserVersionPollingConfig()
        );
    }

    public function resolveRequiredVersion()
    {
        $chromeVersion = $this->resolveBrowserVersion();

        if (!$chromeVersion) {
            return '';
        }

        $majorVersion = strtok($chromeVersion, '.');

        $driverVersionMap = $this->pluginConfig->getBrowserDriverVersionMap();

        foreach ($driverVersionMap as $browserMajor => $driverVersion) {
            if ($majorVersion < $browserMajor) {
                continue;
            }

            return $driverVersion;
        }

        return '';
    }
}
