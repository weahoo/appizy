<?php

namespace Appizy\Command;

use Appizy\ODSReader;
use Appizy\Theme;
use Appizy\WebApp\Tool;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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
        $themeFile = __DIR__ . '/../../theme/' . $themeId . '/' . $themeId . '.info.yml';
        $theme->load($themeFile);

        if ($themeId === 'webapp') {

            $output->writeln("Decompressing file");
            $extractDir = $destinationPath . '/deflated';
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
            $elements = $tool->tool_render(
                null,
                1,
                [
                    'compact css'  => false,
                    'jquery tab'   => false,
                    'print header' => true,
                ]
            );

            $this->renderAndSave(
                $theme,
                [
                    'content'   => $elements['content'],
                    'style'     => $elements['style'],
                    'script'    => $elements['script'],
                    'libraries' => $elements['libraries'],
                    'options'   => json_decode($input->getOption('options'))
                ],
                $destinationPath
            );

        } else {
            $ods = new ODSReader();
            $ods->load($filePath);

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

        self::delTree($destinationPath . '/deflated');
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
