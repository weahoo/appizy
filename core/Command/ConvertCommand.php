<?php

namespace Appizy\Command;

use Appizy\ODSReader;
use Appizy\Theme;
use Appizy\WebApp\Tool;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Twig_Environment;
use Twig_Loader_Filesystem;
use ZipArchive;

class ConvertCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('convert')
            ->setDescription('Convert a spreadsheet to webcontent')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'Which file do you want to convert?'
            )->addArgument(
                'destination',
                InputArgument::OPTIONAL,
                'Where?',
                __DIR__ . '/../../dist'
            )->addOption(
                'theme',
                't',
                InputArgument::OPTIONAL,
                'Theme name',
                'default'
            )->addOption(
                'options',
                'o',
                InputArgument::OPTIONAL,
                'Theme options (as JSON object)',
                '{}'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filePath = $input->getArgument('path');
        $destinationPath = $input->getArgument('destination');
        $themeId = $input->getOption('theme');

        $themeDir = __DIR__ . '/../../theme/' . $themeId;
        $themeConfig = Yaml::parse(
            $themeDir . '/' . $themeId . '.info.yml'
        );

        $theme = new Theme();
        $themeFile = __DIR__ . '/../../theme/' . $themeId . '/' . $themeId . '.info.yml';
        $theme->load($themeFile);

        if ($themeId === 'webapp') {

            $extractDir = $destinationPath . '/deflated';
            $zip = new ZipArchive;
            $zip->open($filePath);
            $zip->extractTo($extractDir);
            $zip->close();

            $xml_path[] = $extractDir . "/styles.xml";
            $xml_path[] = $extractDir . "/content.xml";


            $tool = new Tool(true);

            $tool->tool_parse_wb($xml_path);
            $tool->tool_clean();

            $elements = $tool->tool_render(
                null,
                1,
                [
                    'compact css' => false,
                    'jquery tab' => false,
                    /* 'freeze' => $option_freeze,*/
                    'print header' => true,
                ]
            );

            $this->renderAndSave(
                $theme,
                [
                    'content' => $elements['content'],
                    'style' => $elements['style'],
                    'script' => $elements['script'],
                    'options' => json_decode($input->getOption('options'))
                ],
                $destinationPath
            );

        } else {
            $ods = new ODSReader();
            $ods->load($filePath);

            $this->renderAndSave(
                $theme,
                [
                    'ods' => $ods,
                    'options' => json_decode($input->getOption('options'))
                ],
                $destinationPath
            );

            $this->copyThemeIncludedFiles($theme, $destinationPath);
        }
    }

    /**
     * @param \Appizy\Theme $theme
     * @param               $path
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
     * @param \Appizy\Theme $theme
     * @param               $data
     * @param               $path
     */
    private function renderAndSave($theme, $data, $path)
    {
        $themeDir = $theme->getDirectory();
        $templateFiles = $theme->getTemplateFiles();


        $loader = new Twig_Loader_Filesystem($themeDir);
        $twig = new Twig_Environment(
            $loader, array(// 'cache' => __DIR__ . '/../data',
            )
        );

        foreach ($templateFiles as $file) {
            $renderedTemplate = $twig->render($file, $data);

            $filename = $path . '/' . $file;

            $open = fopen($filename, "w");
            fwrite($open, $renderedTemplate);
            fclose($open);
        }
    }
}
