<?php

namespace Appizy\Command;

use Appizy\ODS;
use Appizy\Parser;
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

        $loader = new Twig_Loader_Filesystem($themeDir);
        $twig = new Twig_Environment(
          $loader, array(// 'cache' => __DIR__ . '/../data',
          )
        );

        $ods = new ODS();
        $ods->load($filePath);

        foreach ($themeConfig['files'] as $file) {
            echo $twig->render(
              $file,
              [
                'ods' => $ods
              ]
            );
        }
    }

    protected function execute2(InputInterface $input, OutputInterface $output)
    {

        $filePath = $input->getArgument('path');
        $destinationPath = $input->getArgument('destination');

        if (empty($destinationPath)) {
            $destinationPath = dirname($filePath);
        }

        if (!is_file($filePath)) {
            $output->writeln("File not found: $filePath. \n");
            throw new Exception();
        }

        try {
            echo "Decompressing file \n";

            $extractDir = $destinationPath . '/deflated';

            $zip = new ZipArchive;
            $zip->open($filePath);
            $zip->extractTo($extractDir);
            $zip->close();

        } catch (Exception $e) {
            echo 'Error while file decompression: ' . $e->getMessage() . "\n";
        }

        $xml_path[] = $extractDir . "/styles.xml";
        $xml_path[] = $extractDir . "/content.xml";


        $tool = new Tool(true);

        try {
            echo "Parsing spreadsheet \n";
            $tool->tool_parse_wb($xml_path);
        } catch (Exception $e) {
            echo 'Error while parsing spreadsheet: ' . $e->getMessage() . "\n";
        }

        try {
            echo "Rendering application \n";
            $tool->tool_clean();

            $elements = $tool->tool_render(
              null,
              1,
              array(
                'compact css'  => false,
                'jquery tab'   => false,
                  /* 'freeze' => $option_freeze,*/
                'print header' => true,
              )
            );
        } catch (Exception $e) {
            echo 'Error while rendering the webapplication: ' . $e->getMessage(
              ) . "\n";
        }

        $htmlTable = $elements['content'];

        // Import variables in local
        extract($elements, EXTR_OVERWRITE);

        // Start output buffering
        ob_start();

        // Include the template file
        include(__DIR__ . '/../View/webapp.tpl.php');

        // End buffering and return its contents
        $htmlTable = ob_get_clean();

        $filename = $destinationPath . "/myappizy.html";
        $open = fopen($filename, "w");
        fwrite($open, $htmlTable);
        fclose($open);

        ob_start();

        include(__DIR__ . '/../View/script.tpl.php');

        $script = ob_get_clean();

        $scriptName = $destinationPath . "/script.js";
        $open = fopen($scriptName, "w");
        fwrite($open, $script);
        fclose($open);


        if (!$input->getOption('keep-deflated')) {
            // Removes temporary file
            self::delTree($extractDir);
            $output->writeln("Temporary files deleted");
        }

    }

    private function delTree($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? self::delTree("$dir/$file") : unlink(
              "$dir/$file"
            );
        }

        return rmdir($dir);
    }
}