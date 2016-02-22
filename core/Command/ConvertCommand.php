<?php

namespace Appizy\Command;

use Appizy\ODSReader;
use Appizy\Theme;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Twig_Environment;
use Twig_Loader_Filesystem;

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

        $ods = new ODSReader();
        $ods->load($filePath);

        $theme = new Theme();
        $themeFile = __DIR__ . '/../../theme/' . $themeId . '/' . $themeId . '.info.yml';
        $theme->load($themeFile);


        $this->renderAndSave(
          $theme,
          [
            'ods'     => $ods,
            'options' => json_decode($input->getOption('options'))
          ],
          $destinationPath
        );

        $this->copyThemeIncludedFiles($theme, $destinationPath);
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