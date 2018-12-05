<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ChromeDriver\Installer;

class EnvironmentAnalyser
{
    /**
     * @var \Vaimo\ChromeDriver\Plugin\Config
     */
    private $pluginConfig;
    
    /**
     * @var \Vaimo\ChromeDriver\Installer\PlatformAnalyser
     */
    private $platformAnalyser;
    
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
        
        $this->platformAnalyser = new \Vaimo\ChromeDriver\Installer\PlatformAnalyser();
        $this->versionResolver = new \Vaimo\ChromeDriver\Installer\VersionResolver();
    }

    public function resolveBrowserVersion()
    {
        $platformCode = $this->platformAnalyser->getPlatformCode();
        $binaryPaths = $this->pluginConfig->getBrowserBinaryPaths();

        if (!isset($binaryPaths[$platformCode])) {
            return '';
        }

        return $this->versionResolver->pollForVersion(
            $binaryPaths[$platformCode],
            $this->pluginConfig->getBrowserVersionPollingConfig()
        );
    }
}
