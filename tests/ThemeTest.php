<?php

namespace Appizy;

use PHPUnit\Framework\TestCase;

class ThemeTest extends TestCase
{
    // TODO: refactor $theme object generation in a setup or before function
    public function testGetThemeDirectory()
    {
        $theme = new \Appizy\Model\Theme();
        $theme->load(__DIR__ . '/fixtures/theme.info.yml');

        $this->assertEquals($theme->getDirectory(), __DIR__ . '/fixtures');
    }

    public function testGetTemplateFiles()
    {
        $theme = new \Appizy\Model\Theme();
        $theme->load(__DIR__ . '/fixtures/theme.info.yml');

        $this->assertEquals(2, count($theme->getTemplateFiles()));
    }

    public function testGetIncludeFiles()
    {
        $theme = new \Appizy\Model\Theme();
        $theme->load(__DIR__ . '/fixtures/theme.info.yml');
        $includedFiles = $theme->getIncludedFiles();

        $this->assertEquals(2, count($includedFiles));
    }
}
