<?php

namespace Lanfest\ChromeDriver;

class Plugin implements \Composer\Plugin\PluginInterface, \Composer\EventDispatcher\EventSubscriberInterface
{
    /**
     * @var \Composer\Composer
     */
    private $composerRuntime;

    /**
     * @var \Composer\IO\IOInterface
     */
    private $cliIO;
    
    public function activate(\Composer\Composer $composer, \Composer\IO\IOInterface $cliIO)
    {
        $this->composerRuntime = $composer;
        $this->cliIO = $cliIO;
    }

    public static function getSubscribedEvents()
    {
        return array(
            \Composer\Script\ScriptEvents::POST_INSTALL_CMD => 'installDriver',
            \Composer\Script\ScriptEvents::POST_UPDATE_CMD => 'installDriver',
        );
    }

    public function installDriver()
    {
        $driverInstaller = new \Lanfest\WebDriverBinaryDownloader\Installer(
            $this->composerRuntime,
            $this->cliIO
        );
        
        $pluginConfig = new \Lanfest\ChromeDriver\Plugin\Config(
            $this->composerRuntime->getPackage()
        );

        $driverInstaller->executeWithConfig($pluginConfig);
    }
}
