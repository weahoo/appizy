<?php

namespace Appizy\Model;

use Symfony\Component\Yaml\Yaml;

class Theme
{
    /** @var array $rawConfig */
    protected $rawConfig;

    /** @var string $directory */
    protected $directory;

    public function __construct()
    {
    }

    public function load($configFile)
    {
        if (is_file($configFile)) {
            $this->rawConfig = Yaml::parse(file_get_contents($configFile));
        } else {
            echo "File $configFile not found";
        }

        $this->directory = dirname($configFile);
    }

    public function getDirectory()
    {
        return $this->directory;
    }

    public function getIncludedFiles()
    {
        $includedFiles = [];

        if (array_key_exists('includes', $this->rawConfig)) {
            $includedFiles = $this->rawConfig['includes'];
        }

        return $includedFiles;
    }

    public function getTemplateFiles()
    {
        $includedFiles = [];

        if (array_key_exists('templates', $this->rawConfig)) {
            $includedFiles = $this->rawConfig['templates'];
        }

        return $includedFiles;
    }
}
