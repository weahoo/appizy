<?php

namespace Appizy\Core\Command;

use Appizy\Core\Theme;
use Appizy\WebApp\OpenDocumentParser;
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
        register_shutdown_function('\Appizy\Core\ErrorHandler::fatalHandler' );
        set_error_handler('\Appizy\Core\ErrorHandler::handle');

        $filePath = $input->getArgument('source');

        $destinationPath = $input->getArgument('destination');
        if (empty($destinationPath)) {
            $destinationPath = dirname($filePath);
        }

        $themeId = $input->getOption('theme');

        $theme = new Theme();
        $themeFile = __DIR__ . '/../../../../theme/' . $themeId . '/' . $themeId . '.info.yml';
        $theme->load($themeFile);


        $output->writeln("Decompressing file");
        $extractDir = $destinationPath . '/deflated';
//        $filePath = APPIZY_BASE_DIR . DIRECTORY_SEPARATOR . $filePath;
        $zip = new ZipArchive;
        $zip->open($filePath);
        $zip->extractTo($extractDir);
        $zip->close();

        $xmlFilesPath[] = $extractDir . "/styles.xml";
        $xmlFilesPath[] = $extractDir . "/content.xml";

        $output->writeln("Parsing spreadsheet");
        $maxParsedCells = $input->getOption('max-cells');
        $OpenDocumentParser = new OpenDocumentParser($maxParsedCells);
        $spreadsheet = $OpenDocumentParser->parse($xmlFilesPath);
        $spreadsheet->setFormulaDependenciesAsInputCells();
        $spreadsheet->cleanStyles();
        $spreadsheet->clean();

        $output->writeln("Rendering application");
        $elements = $spreadsheet->tool_render();

        $options = $this->getOptions($input);

        $this->renderAndSave(
            $theme,
            [
                'spreadSheet' => $spreadsheet,
                'content' => $elements['content'],
                'style' => $elements['style'],
                'script' => $elements['script'],
                'options' => $options,
                'libraries' => $elements['libraries']
            ],
            $destinationPath
        );

        $this->copyThemeIncludedFiles($theme, $destinationPath);


//        self::delTree($destinationPath . '/deflated');
    }

    /**
     * @param \Appizy\Core\Theme $theme
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
     * @param \Appizy\Core\Theme $theme
     * @param array $data
     * @param string $path
     */
    private function renderAndSave($theme, $data, $path)
    {
        $themeDir = $theme->getDirectory();
        $templateFiles = $theme->getTemplateFiles();


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

    /**
     * @param InputInterface $input
     * @return mixed
     */
    public function getOptions(InputInterface $input)
    {
        $options = json_decode($input->getOption('options'));

        $optionTab = $input->getOption('tabs');
        switch ($optionTab) {
            case 'list':
            case 'js':
            case 'none':
                $options->tabs = $optionTab;
                break;
            default:
                $options->tabs = 'js';
        }

        return $options;
    }
}
