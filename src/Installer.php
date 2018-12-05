<?php
/**
 * Copyright © Laurent Baey. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ChromeDriver;

use Vaimo\ChromeDriver\Plugin\Config;

class Installer
{
    /**
     * @var \Composer\Composer
     */
    private $composerRuntime;

    /**
     * @var \Composer\IO\IOInterface
     */
    private $io;

    /**
     * @var \Composer\Cache
     */
    private $cacheManager;

    /**
     * @var \Vaimo\ChromeDriver\Utils\OsDetector
     */
    private $osDetector;

    /**
     * @var \Vaimo\ChromeDriver\Installer\VersionResolver
     */
    private $versionResolver;
    
    /**
     * @var \Vaimo\ChromeDriver\Installer\Utils 
     */
    private $utils;

    /**
     * @param \Composer\Composer $composerRuntime
     * @param \Composer\IO\IOInterface $io
     */
    public function __construct(
        \Composer\Composer $composerRuntime,
        \Composer\IO\IOInterface $io
    ) {
        $this->composerRuntime = $composerRuntime;
        $this->io = $io;
        
        $this->osDetector = new \Vaimo\ChromeDriver\Utils\OsDetector();
        $this->utils = new \Vaimo\ChromeDriver\Installer\Utils();
        $this->versionResolver = new \Vaimo\ChromeDriver\Installer\VersionResolver();
    }
    
    private function resolveRequiredVersion($pluginConfig)
    {
        $preferences = $pluginConfig->getPreferences();
        $requestConfig = $pluginConfig->getRequestUrlConfig();

        $version = $preferences['version'];

        $browserDetector = new \Vaimo\ChromeDriver\Installer\BrowserDetector($pluginConfig);

        if (!$preferences['version']) {
            $version = $browserDetector->resolveRequiredVersion();

            if ($version && $this->io->isVerbose()) {
                $this->io->write('<info>ChromeDriver version chosen base on installed Chrome browser</info>');
            }

            if (!$version) {
                $versionCheckUrl = $requestConfig[Config::REQUEST_VERSION];

                if ($this->io->isVerbose()) {
                    $this->io->write('<info>Polling for the latest version of ChromeDriver</info>');
                }

                $version = trim(@file_get_contents($versionCheckUrl));
            }
        }

        $this->utils->validateVersion($version);
        
        return $version;
    }
    
    public function installDriver()
    {
        $pluginConfig = new \Vaimo\ChromeDriver\Plugin\Config($this->composerRuntime->getPackage());
        
        $requestConfig = $pluginConfig->getRequestUrlConfig();
        
        $version = $this->resolveRequiredVersion($pluginConfig);

        if ($this->io->isVerbose()) {
            $this->io->write(sprintf('<comment>Using version %s</comment>', $version));
        }

        $platformCode = $this->osDetector->getPlatformCode();
        
        $executableNames = $pluginConfig->getExecutableFileNames();
        $remoteFiles = $pluginConfig->getRemoteFileNames();

        if (!isset($executableNames[$platformCode], $remoteFiles[$platformCode])) {
            throw new \Exception('Failed to resolve a file for the platform. Download driver manually');
        }
        
        $executableName = $executableNames[$platformCode];
        $remoteFileName = $remoteFiles[$platformCode];

        $binaryDir = $this->composerRuntime->getConfig()
            ->get('bin-dir');

        $chromeDriverPath = $binaryDir . DIRECTORY_SEPARATOR . $executableName;

        $currentVersion = $this->versionResolver->pollForVersion(
            [$chromeDriverPath], 
            $pluginConfig->getDriverVersionPollingConfig()
        );
        
        if (strpos($currentVersion, $version) === 0) {
            if ($this->io->isVerbose()) {
                $this->io->write(sprintf('Required version (v%s) already installed', $version));
            }

            return;
        }
        
        $this->io->write(sprintf('<info>Installing <comment>ChromeDriver</comment> (v%s)</info>', $version));

        $cacheManager = $this->getCacheManager();
        
        $fs = new \Composer\Util\Filesystem();

        $versionCache = $cacheManager->getRoot() . $version;
        
        $fs->ensureDirectoryExists($versionCache);
        $fs->ensureDirectoryExists($binaryDir);

        $chromeDriverArchiveCacheFileName = $versionCache . DIRECTORY_SEPARATOR . $executableName;

        if (!$cacheManager->isEnabled() || !file_exists($chromeDriverArchiveCacheFileName)) {
            $fileUrl = $this->utils->stringFromTemplate(
                $requestConfig[Config::REQUEST_DOWNLOAD], 
                ['version' => $version, 'file' => $remoteFileName]
            );

            $headers = $this->utils->getHeaders($fileUrl);
            $remoteTag = trim($headers['ETag'], '" ');

            if (!isset($headers['ETag'])) {
                throw new \Exception('Failed to acquire entity tag (ETag) from Google Storage API headers');
            }

            $this->io->write(sprintf(
                'Downloading ChromeDriver version %s for %s (%s)',
                $version,
                $this->osDetector->getPlatformName(),
                $remoteTag
            ));

            @file_put_contents(
                $chromeDriverArchiveCacheFileName,
                @fopen($fileUrl, 'r')
            );

            $localTag = md5_file($chromeDriverArchiveCacheFileName);

            if ($localTag !== $remoteTag) {
                unlink($chromeDriverArchiveCacheFileName);

                throw new \Exception(
                    sprintf('File validation failed: %s != %s', $localTag, $remoteTag)
                );
            }
        } else {
            if ($this->io->isVerbose()) {
                $this->io->write(sprintf('Using cached version of %s', $remoteFileName));
            } else {
                $this->io->write('Loading from cache');
            }
        }

        $this->io->write(sprintf('<info>Done</info>', $version));

        $archive = new \ZipArchive();
        
        $archive->open($chromeDriverArchiveCacheFileName);
        $archive->extractTo($binaryDir);

        if ($platformCode !== \Vaimo\ChromeDriver\Utils\OsDetector::TYPE_WIN32) {
            chmod($binaryDir . DIRECTORY_SEPARATOR . $executableName, 0755);
        }
    }

    private function getCacheManager()
    {
        if ($this->cacheManager === null) {
            $config = $this->composerRuntime->getConfig();

            $this->cacheManager = new \Composer\Cache(
                $this->io,
                implode(DIRECTORY_SEPARATOR, [
                    $config->get('cache-dir'),
                    'files',
                    'vaimo-chromedriver',
                    'downloaded-bin'
                ])
            );
        }

        return $this->cacheManager;
    }
}
