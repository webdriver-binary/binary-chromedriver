<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ChromeDriver\Installer;

class VersionResolver
{
    /**
     * @var \Vaimo\ChromeDriver\Installer\Utils
     */
    private $installedUtils;

    public function __construct() 
    {
        $this->installedUtils = new \Vaimo\ChromeDriver\Installer\Utils();
    }
    
    public function pollForVersion(array $binaryPaths, array $versionPollingConfig)
    {
        $processExecutor = new \Composer\Util\ProcessExecutor();
        
        $processExecutor::setTimeout(10);

        foreach ($binaryPaths as $path) {
            if (!is_executable($path)) {
                continue;
            }

            foreach ($versionPollingConfig as $callTemplate => $resultPatterns) {
                $output = '';

                $processExecutor->execute(sprintf($callTemplate, $path), $output);

                $output = trim($output);

                foreach ($resultPatterns as $pattern) {
                    $matches = sscanf($output, $pattern);

                    if (!is_array($matches) || !$matches) {
                        continue;
                    }

                    $result = reset($matches);

                    try {
                        $this->installedUtils->validateVersion($result);
                    } catch (\Exception $exception) {
                        continue;
                    }

                    return $result;
                }
            }
        }

        return '';
    }
}
