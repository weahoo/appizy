<?php

namespace Appizy\Command;

use Appizy\Model\Theme;
use Appizy\Service\SpreadsheetRenderService;
use Appizy\Service\ThemeReadService;
use Appizy\Parser\OpenDocumentParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use tidy;
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
                'source',
                InputArgument::REQUIRED,
                'Which file do you want to convert?'
            )->addArgument(
                'destination',
                InputArgument::OPTIONAL,
                'Set to source directory if empty'
            )->addOption(
                'theme',
                't',
                InputArgument::OPTIONAL,
                'Theme name',
                'webapp'
            )->addOption(
                'tabs',
                null,
                InputArgument::OPTIONAL,
                'Tabs options (none|js|list)',
                'js'
            )->addOption(
                'options',
                'o',
                InputArgument::OPTIONAL,
                'Theme options (as JSON object)',
                '{}'
            )->addOption(
                'max-cells',
                'm',
                InputArgument::OPTIONAL,
                'Max parsed cells number',
                -1
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        register_shutdown_function('\Appizy\ErrorHandler::fatalHandler');
        set_error_handler('\Appizy\ErrorHandler::handle');

        $filePath = $input->getArgument('source');

        $destinationPath = $input->getArgument('destination');
        if (empty($destinationPath)) {
            $destinationPath = dirname($filePath);
        }

        $output->writeln("Decompressing file");
        $output->writeln("Parsing spreadsheet");

        $maxParsedCells = $input->getOption('max-cells');
        $OpenDocumentParser = new OpenDocumentParser($maxParsedCells);
        $spreadsheet = $OpenDocumentParser->parse($filePath);

        $output->writeln("Rendering application");


        $themeOptions = json_decode($input->getOption('options'));
        $theme = $input->getOption('theme');
        $renderService = new SpreadsheetRenderService($spreadsheet, $theme);
        $renderService->render($destinationPath, $themeOptions);
    }
}
