<?php

namespace Appizy;

class Parser
{
    /** @var  string $tempDir */
    var $tempDir;

    public function __construct()
    {
        $this->offset = 0;
        $this->tempDir = './dist';
    }

    /**
     * @param string $file
     * @return \DOMXPath
     */
    public static function parse($file)
    {
        $tmp = self::getTmpDir();
        copy($file, $tmp . '/' . basename($file));
        $path = $tmp . '/' . basename($file);
        $uid = uniqid();
        $tempDir = $tmp . '/' . $uid;
        mkdir($tempDir);
        shell_exec(
          'unzip ' . escapeshellarg($path) . ' -d ' . escapeshellarg(
            $tempDir
          )
        );

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
    private function getTmpDir()
    {

        return './dist';
    }

    /**
     * @param string $dir
     * @return bool
     */
    static function deleteTree($dir)
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