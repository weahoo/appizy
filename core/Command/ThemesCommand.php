<?php

namespace Appizy\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class ThemesCommand extends Command
{
    protected function configure()
    {
        $this
          ->setName('themes')
          ->setDescription(
            'Show which themes are available and their options'
          );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $finder = new Finder();

        $finder->files()
          ->in(__DIR__ . '/../../theme')
          ->depth(1)
          ->name('*.info.yml');

        $themesInfo = [];

        /** @var SplFileInfo $themeInfoFile */
        foreach ($finder as $themeInfoFile) {
            $themesInfo[] = Yaml::parse(
              file_get_contents(
                $themeInfoFile->getPathname()
              )
            );
        }

        $output->writeln(json_encode($themesInfo));
    }
}