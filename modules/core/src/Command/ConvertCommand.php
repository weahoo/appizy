<?php

namespace Appizy\Core\Command;

use Appizy\Core\Theme;
use Appizy\WebApp\Tool;
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
                'options',
                'o',
                InputArgument::OPTIONAL,
                'Theme options (as JSON object)',
                '{}'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
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
        $extractDir = APPIZY_BASE_DIR . DIRECTORY_SEPARATOR . $destinationPath . '/deflated';
        $filePath = APPIZY_BASE_DIR . DIRECTORY_SEPARATOR . $filePath;
        $zip = new ZipArchive;
        $zip->open($filePath);
        $zip->extractTo($extractDir);
        $zip->close();

        $xml_path[] = $extractDir . "/styles.xml";
        $xml_path[] = $extractDir . "/content.xml";


        $tool = new Tool(true);

        $output->writeln("Parsing spreadsheet");
        $tool->tool_parse_wb($xml_path);
        $tool->tool_clean();

        $output->writeln("Rendering application");
        $elements = $tool->tool_render();

        $this->renderAndSave(
            $theme,
            [
                'spreadSheet' => $tool,
                'content'     => $elements['content'],
                'style'       => $elements['style'],
                'script'      => $elements['script'],
                'options'     => json_decode($input->getOption('options')),
                'libraries'   => $elements['libraries']
            ],
            $destinationPath
        );

        $this->copyThemeIncludedFiles($theme, $destinationPath);


//        self::delTree($destinationPath . '/deflated');
    }

    /**
     * @param \Appizy\Core\Theme $theme
     * @param string             $path
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
     * @param array              $data
     * @param string             $path
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
            $filename = APPIZY_BASE_DIR . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $fileName;

            if (preg_match('/\.html/', $fileName)) {
//                $renderedTemplate = $this->formatHTML($renderedTemplate);
            }

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
            'indent'      => true,
            'output-html' => true,
            'wrap'        => '1000'
        ];

        $tidy = new tidy();
        $tidy->parseString($html, $config, 'utf8');
        $tidy->cleanRepair();

        return tidy_get_output($tidy);
    }
}
