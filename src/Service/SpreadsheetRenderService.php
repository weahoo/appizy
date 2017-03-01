<?php

namespace Appizy\Service;

use Appizy\Model\Theme;
use Appizy\Tool;
use tidy;
use Twig_Environment;
use Twig_Loader_Filesystem;

class SpreadsheetRenderService
{
    /** @var Tool */
    private $spreadsheet;
    /** @var Theme  */
    private $theme;

    /**
     * SpreadsheetRenderService constructor.
     * @param Tool $spreadsheet
     * @param string $themeName
     */
    function __construct($spreadsheet, $themeName)
    {
        $this->spreadsheet = $spreadsheet;

        $themeReadService = new ThemeReadService();
        $this->theme = $themeReadService->getThemeByName($themeName);
    }

    public function render($destinationPath, $themeOptions) {
        $this->spreadsheet->setFormulaDependenciesAsInputCells();
        $this->spreadsheet->cleanStyles();
        $this->spreadsheet->clean();
        $elements = $this->spreadsheet->render();

        $this->renderAndSave(
            [
                'spreadSheet' => $this->spreadsheet,
                'content' => $elements['content'],
                'style' => $elements['style'],
                'script' => $elements['script'],
                'options' => $themeOptions,
                'libraries' => $elements['libraries']
            ],
            $destinationPath
        );

        $this->copyThemeIncludedFiles($this->theme, $destinationPath);
    }


    /**
     * @param Theme $theme
     * @param string $path
     */
    private function copyThemeIncludedFiles($theme, $path)
    {
        $themeDir = $theme->getDirectory();
        $includedFiles = $theme->getIncludedFiles();

        foreach ($includedFiles as $file) {
            copy($themeDir . '/' . $file, $path . '/' . $file);
        }
    }

    /**
     * @param \\Appizy\Model\Theme $theme
     * @param array $data
     * @param string $path
     */
    private function renderAndSave($data, $path)
    {
        $themeDir = $this->theme->getDirectory();
        $templateFiles = $this->theme->getTemplateFiles();


        $loader = new Twig_Loader_Filesystem($themeDir);
        $twig = new Twig_Environment($loader, [
            // 'cache' => __DIR__ . '/../data',
        ]);

        foreach ($templateFiles as $fileName) {
            $renderedTemplate = $twig->render($fileName, $data);

            $fileName = str_replace('.twig', '', $fileName);
            $filename = $path . DIRECTORY_SEPARATOR . $fileName;

//            if (preg_match('/\.html/', $fileName)) {
//                $renderedTemplate = $this->formatHTML($renderedTemplate);
//            }

            $open = fopen($filename, "w");
            fwrite($open, $renderedTemplate);
            fclose($open);
        }
    }

    /**
     * @param string $dir
     * @return mixed
     */
    private function delTree($dir)
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            if (is_dir("$dir/$file")) {
                self::delTree("$dir/$file");
            } else {
                unlink("$dir/$file");
            }
        }

        return rmdir($dir);
    }

    /**
     * @param string $html
     * @return string
     */
    private function formatHTML($html)
    {
        $config = [
            'indent' => true,
            'output-html' => true,
            'wrap' => '1000'
        ];

        $tidy = new tidy();
        $tidy->parseString($html, $config, 'utf8');
        $tidy->cleanRepair();

        return tidy_get_output($tidy);
    }

}
