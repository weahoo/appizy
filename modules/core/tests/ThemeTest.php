<?php

class ThemeTest extends PHPUnit_Framework_TestCase
{
    // TODO: refactor $theme object generation in a setup or before function

    public function testGetThemeDirectory(){
        $theme = new \Appizy\Core\Theme();
        $theme->load(__DIR__ . '/fixtures/theme.info.yml');

        $this->assertEquals($theme->getDirectory(), __DIR__ . '/fixtures');
    }

    public function testGetTemplateFiles(){
        $theme = new \Appizy\Core\Theme();
        $theme->load(__DIR__ . '/fixtures/theme.info.yml');

        $this->assertEquals(2, count($theme->getTemplateFiles()));
    }

    public function testGetIncludeFiles()
    {
        $theme = new \Appizy\Core\Theme();
        $theme->load(__DIR__ . '/fixtures/theme.info.yml');
        $includedFiles = $theme->getIncludedFiles();

        $this->assertEquals(2, count($includedFiles));
    }
}
 