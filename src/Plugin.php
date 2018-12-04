<?php
/**
 * Copyright © Laurent Baey. All rights reserved.
 * See LICENSE.txt for license details.
 * 
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ChromeDriver;

use Composer\Cache;
use Composer\Composer;
use Composer\Config;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Package\CompletePackage;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem;
use Composer\Util\RemoteFilesystem;
use Composer\Util\ProcessExecutor;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    const UNKNOWN = 'unknown';
    const LINUX32 = 'linux32';
    const LINUX64 = 'linux64';
    const MAC64 = 'mac64';
    const WIN32 = 'win32';

    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Composer\Semver\VersionParser
     */
    protected $versionParser;

    public function __construct()
    {
        $this->versionParser = new \Composer\Semver\VersionParser();
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            ScriptEvents::POST_INSTALL_CMD => 'onPostInstallCmd',
            ScriptEvents::POST_UPDATE_CMD => 'onPostUpdateCmd',
        );
    }

    /**
     * @param Composer $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->config = $this->composer->getConfig();
        $this->cache = new Cache(
            $this->io,
            implode(DIRECTORY_SEPARATOR, [
                $this->config->get('cache-dir'),
                'files',
                'lbaey-chromedriver',
                'downloaded-bin'
            ])
        );
    }

    /**
     * Handle post install command events.
     *
     * @param Event $event The event to handle.
     *
     */
    public function onPostInstallCmd(Event $event)
    {
        $this->installDriver($event);
    }

    /**
     * Handle post update command events.
     *
     * @param Event $event The event to handle.
     *
     */
    public function onPostUpdateCmd(Event $event)
    {
        $this->installDriver($event);
    }
    
    protected function installDriver(Event $event)
    {
        $baseUrl = 'https://chromedriver.storage.googleapis.com';
        $downloadUrlTemplate = '{{base}}/{{version}}/{{file}}';
        $versionUrlTemplate = '{{base}}/LATEST_RELEASE';

        $config = $this->getPluginConfig();

        if (!$config['version']) {
            $versionCheckUrl = $this->stringFromTemplate($versionUrlTemplate, array(
                'base' => $baseUrl
            ));

            if ($this->io->isVerbose()) {
                $this->io->write('<info>Polling for the latest version of ChromeDriver</info>');
            }

            $version = trim(@file_get_contents($versionCheckUrl));
        } else {
            $version = $config['version'];
        }

        $this->validateVersion($version);

        if ($this->io->isVerbose()) {
            $this->io->write(sprintf('<comment>Using version %s</comment>', $version));
        }

        $platformType = $this->getPlatform();

        $executableName = $this->getExecutableFileName();

        $chromeDriverPath = $this->config->get('bin-dir') . DIRECTORY_SEPARATOR . $executableName;
        $output = '';

        if (file_exists($chromeDriverPath) && is_executable($chromeDriverPath)) {
            $processExecutor = new ProcessExecutor($this->io);
            $processExecutor::setTimeout(10);
            $processExecutor->execute($chromeDriverPath . ' --version', $output);

            if (strpos($output, 'ChromeDriver ' . $version) === 0) {
                if ($this->io->isVerbose()) {
                    $this->io->write(sprintf('ChromeDriver v%s is already installed', $version));
                }

                return;
            }
        }

        $this->io->write(sprintf('<info>Installing <comment>ChromeDriver</comment> (v%s)</info>', $version));

        $fs = new Filesystem();
        $fs->ensureDirectoryExists($this->cache->getRoot() . $version);
        $fs->ensureDirectoryExists($this->config->get('bin-dir'));

        $chromeDriverArchiveCacheFileName = $this->cache->getRoot() . $version . DIRECTORY_SEPARATOR . $executableName;

        if (!$this->cache->isEnabled() || !file_exists($chromeDriverArchiveCacheFileName)) {
            $platformNames = $this->getPlatformNames();

            $fileUrl = $this->stringFromTemplate($downloadUrlTemplate, array(
                'base' => $baseUrl,
                'version' => $version,
                'file' => $this->getRemoteFileName()
            ));

            $headers = $this->getHeaders($fileUrl);
            $remoteTag = trim($headers['ETag'], '" ');

            if (!isset($headers['ETag'])) {
                throw new \Exception('Failed to acquire entity tag (ETag) from Google Storage API headers');
            }

            $this->io->write(sprintf(
                'Downloading ChromeDriver version %s for %s (%s)',
                $version,
                $platformNames[$platformType],
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
                $this->io->write(sprintf('Using cached version of %s', $this->getRemoteFileName()));
            } else {
                $this->io->write('Loading from cache');
            }
        }

        $this->io->write(sprintf('<info>Done</info>', $version));

        $archive = new \ZipArchive();
        $archive->open($chromeDriverArchiveCacheFileName);
        $archive->extractTo($this->config->get('bin-dir'));

        if ($this->getPlatform() !== self::WIN32) {
            chmod($this->config->get('bin-dir') . DIRECTORY_SEPARATOR . $executableName, 0755);
        }
    }

    private function stringFromTemplate($template, array $values)
    {
        $variables = array_combine(
            array_map(function ($name) {
                return sprintf('{{%s}}', $name);
            }, array_keys($values)),
            $values
        );

        return str_replace(array_keys($variables), $variables, $template);
    }

    private function validateVersion($version)
    {
        try {
            $this->versionParser->parseConstraints($version);
        } catch (\UnexpectedValueException $exception) {
            throw new \Exception(sprintf('Incorrect version string: "%s"', $version));
        }
    }

    private function getHeaders($url)
    {
        $headers = get_headers($url);

        return array_combine(
            array_map(function ($value) {
                return strtok($value, ':');
            }, $headers),
            array_map(function ($value) {
                return trim(substr($value, strpos($value, ':')), ': ');
            }, $headers)
        );
    }

    private function getPluginConfig()
    {
        $rootPackage = $this->composer->getPackage();

        $extra = $rootPackage->getExtra();

        $defaults = array(
            'version' => null
        );

        $config =  array_replace(
            $defaults,
            isset($extra['chromedriver']) ? $extra['chromedriver'] : array()
        );

        if (isset($config['chromedriver-version'])) {
            $config['version'] = $config['chromedriver-version'];
        }
    }

    private function getPlatform()
    {
        if (stripos(PHP_OS, 'win') === 0) {
            return self::WIN32;
        } elseif (stripos(PHP_OS, 'darwin') === 0) {
            return self::MAC64;
        } elseif (stripos(PHP_OS, 'linux') === 0) {
            if (PHP_INT_SIZE === 8) {
                return self::LINUX64;
            } else {
                return self::LINUX32;
            }
        }

        $this->io->writeError('Could not guess your platform, download chromedriver manually.');

        return null;
    }

    private function getRemoteFileName()
    {
        switch ($this->getPlatform()) {
            case self::LINUX32:
                return 'chromedriver_linux32.zip';
            case self::LINUX64:
                return 'chromedriver_linux64.zip';
            case self::MAC64:
                return 'chromedriver_mac64.zip';
            case self::WIN32:
                return 'chromedriver_win32.zip';
            default:
                throw new \Exception('Platform is not set.');
        }
    }

    private function getExecutableFileName()
    {
        switch ($this->getPlatform()) {
            case self::LINUX32:
            case self::LINUX64:
            case self::MAC64:
                return 'chromedriver';
            case self::WIN32:
                return 'chromedriver.exe';
            default:
                throw new \Exception('Platform is not set.');
        }
    }

    private function getPlatformNames()
    {
        return [
            self::LINUX32 => 'Linux 32Bits',
            self::LINUX64 => 'Linux 64Bits',
            self::MAC64 => 'Mac OS X',
            self::WIN32 => 'Windows'
        ];
    }
}
