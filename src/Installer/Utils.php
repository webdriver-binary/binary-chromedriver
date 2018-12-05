<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ChromeDriver\Installer;

class Utils
{
    /**
     * @var \Composer\Semver\VersionParser
     */
    private $versionParser;

    public function __construct() 
    {
        $this->versionParser = new \Composer\Semver\VersionParser();
    }
    
    public function stringFromTemplate($template, array $values)
    {
        $variables = array_combine(
            array_map(function ($name) {
                return sprintf('{{%s}}', $name);
            }, array_keys($values)),
            $values
        );

        return str_replace(array_keys($variables), $variables, $template);
    }

    public function validateVersion($version)
    {
        try {
            $this->versionParser->parseConstraints($version);
        } catch (\UnexpectedValueException $exception) {
            throw new \Exception(sprintf('Incorrect version string: "%s"', $version));
        }
    }

    public function getHeaders($url)
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
}
