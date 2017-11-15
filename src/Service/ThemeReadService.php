<?php

namespace Appizy\Service;

use Appizy\Model\Theme;

class ThemeReadService
{
    public function getThemeByName($themeName)
    {
        $theme = new Theme();
        $themeFile = __DIR__ . '/../../theme/' . $themeName . '/' . $themeName . '.info.yml';
        $theme->load($themeFile);

        return $theme;
    }
}
