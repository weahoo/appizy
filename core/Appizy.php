<?php

namespace Appizy;

use Appizy\Parser;
use Symfony\Component\Yaml\Yaml;
use Twig_Environment;
use Twig_Loader_Filesystem;

require_once __DIR__ . '/../vendor/autoload.php';

$themeDir = __DIR__ . '/../theme/default';
$themeConfig = Yaml::parse(
  $themeDir . '/default.info.yml'
);

$loader = new Twig_Loader_Filesystem($themeDir);
$twig = new Twig_Environment(
  $loader, array(// 'cache' => __DIR__ . '/../data',
  )
);

$xpath = Parser::parse(__DIR__ . '/Tests/Fixtures/demo-appizy.ods');

foreach ($xpath->query('//table:table') as $node) {
    $name = $node->getAttribute('table:name');
    printf("%s \n", $name);
}

foreach ($themeConfig['files'] as $file) {
    echo $twig->render($file, array('name' => $name));
}

