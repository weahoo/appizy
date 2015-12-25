<?php

namespace Appizy;

class Parser
{
    public function __construct(){
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
        $path = $tmp . '/' . basename($file);
        $uid = uniqid();
        mkdir($tmp . '/' . $uid);
        shell_exec(
          'unzip ' . escapeshellarg($path) . ' -d ' . escapeshellarg(
            $tmp . '/' . $uid
          )
        );

        $document = new \DOMDocument();
        $document->loadXML(file_get_contents($tmp . '/' . $uid . '/content.xml'));
        $xpath = new \DOMXPath($document);

        return $xpath;
    }

    /**
     * @return string
     */
    private function getTmpDir()
    {

        return './dist';
    }
}