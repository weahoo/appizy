<?php

namespace Appizy\Command;

use Appizy\ODS;
use Exception;
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
          ->setName('appizy')
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
          );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filePath = $input->getArgument('path');
        $destinationPath = $input->getArgument('destination');
        $themeId = $input->getOption('theme');

        $themeDir = __DIR__ . '/../../theme/default';
        $themeConfig = Yaml::parse(
          $themeDir . '/default.info.yml'
        );

        $ods = new ODS();
        $ods->load($filePath);

        foreach ($themeConfig['files'] as $file) {
            $this->renderAndSave($file, $ods, $destinationPath);
        }
    }

    /**
     *
     */
    private function renderAndSave($template, $data, $path)
    {
        $themeDir = __DIR__ . '/../../theme/default';

        $loader = new Twig_Loader_Filesystem($themeDir);
        $twig = new Twig_Environment(
          $loader, array(// 'cache' => __DIR__ . '/../data',
          )
        );

        $renderedTemplate = $twig->render(
          $template,
          [
            'ods' => $data
          ]
        );

        echo $renderedTemplate;
        echo $template;
        $filename = $path . '/' . $template;

        $open = fopen($filename, "w");
        fwrite($open, $renderedTemplate);
        fclose($open);
    }
}