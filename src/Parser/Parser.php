<?php

namespace Appizy\Parser;

use ZipArchive;

class Parser
{
    /** @var  string $tempDir */
    protected $tempDir;

    public function __construct()
    {
        $this->offset = 0;
    }

    /**
     * @param string $file
     * @return \DOMXPath
     */
    public static function parse($file)
    {
        $tmp = self::getTmpDir();
        copy($file, $tmp . '/' . basename($file));
        $path =
            $tmp . '/' . basename($file);
        $uid = uniqid();
        $tempDir = $tmp . '/' . $uid;
        mkdir($tempDir);

        $zip = new ZipArchive;
        $zip->open($path);
        $zip->extractTo($tempDir);

        $document = new \DOMDocument();
        $document->loadXML(
            file_get_contents($tempDir . '/content.xml')
        );
        $xpath = new \DOMXPath($document);

        self::deleteTree($tempDir);

        return $xpath;
    }

    /**
     * @return string
     */
    private static function getTmpDir()
    {
        return sys_get_temp_dir();
    }

    /**
     * @param string $dir
     * @return bool
     */
    private static function deleteTree($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? self::deleteTree("$dir/$file") : unlink(
                "$dir/$file"
            );
        }

        return rmdir($dir);
    }
}
