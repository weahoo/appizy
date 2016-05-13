<?php

// Register a shutdown function to catch FATAL errors
register_shutdown_function('fatalHandler');

$filePath = $argv[1];

if (!is_file($filePath)) {
    echo("File not found: $filePath. \n");
    throw new Exception();
}

try {
    echo "Decompressing file \n";

    $fileDir = dirname($filePath);
    $extractDir = $fileDir . '/deflated';

    $zip = new ZipArchive;
    $zip->open($filePath);
    $zip->extractTo($extractDir);
    $zip->close();

} catch (Exception $e) {
    echo 'Error while file decompression: ' . $e->getMessage() . "\n";
}

$xml_path[] = $extractDir . "/styles.xml";
$xml_path[] = $extractDir . "/content.xml";

include('Tool.php');

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

    $elements = $tool->tool_render(null, 1, array(
        'compact css'  => false,
        'jquery tab'   => false,
        /* 'freeze' => $option_freeze,*/
        'print header' => true,
    ));
} catch (Exception $e) {
    echo 'Error while rendering the webapplication: ' . $e->getMessage() . "\n";
}

$htmlTable = $elements['content'];

// Import variables in local
extract($elements, EXTR_OVERWRITE);

// Start output buffering
ob_start();

// Include the template file
include('webapp.tpl.php');

// End buffering and return its contents
$htmlTable = ob_get_clean();

$filename = $fileDir . "/myappizy.html";
$open = fopen($filename, "w");
fwrite($open, $htmlTable);
fclose($open);

// Removes temporary file
delTree($extractDir);

function delTree($dir)
{
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
    }

    return rmdir($dir);
}


function fatalHandler()
{
    $error = error_get_last();

    if ($error !== null) {
        $message = $error["message"];

        if (preg_match('/Allowed memory size of (\\d+) bytes exhausted/', $message)) {
            echo "X File too big. Have look at the FAQ for more information. \n";
        } else {
            echo "X Unknown fatal error. Please report to hello@appizy.com: $message";
        }
    }
}