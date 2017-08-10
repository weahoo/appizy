<?php

namespace Appizy;

trait ArrayTrait
{

    function array_flat($non_flat_array = array())
    {
        foreach ($non_flat_array as $content) {
            $flat_array = array();
            array_walk_recursive($content, function ($a) use (&$flat_array) {
                $flat_array[] = $a;
            });
        }

        return $flat_array;
    }

    function getArrayValueIfExists($array, $key)
    {
        if (isset($array[$key])) {
            return $array[$key];
        }
    }

    function compact_code($some_code)
    {
        $compact_code = $some_code;

        // Remove comments
        $compact_code = preg_replace('~(?://)[^\r\n]*|/\*.*?\*/~s', '', $compact_code);

        // Remove tabs, spaces, newlines, etc.
        $compact_code = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $compact_code);

        // Add breakline to see something
        $compact_code = str_replace(array("}."), '}' . "\n" . '.', $compact_code);
        $compact_code = str_replace('}#', '}' . "\n" . "#", $compact_code);

        return $compact_code;
    }

}
