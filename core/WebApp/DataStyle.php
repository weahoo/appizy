<?php

namespace Appizy\WebApp;

class DataStyle
{
    var $id;

    var $decimal_places;
    var $min_int_digit;
    var $maps;
    var $prefix;
    var $suffix;

    function __construct($id)
    {
        $this->id = $id;
    }

    function data_style_set_prefix($prefix)
    {
        // Remove euro sign
        $prefix = str_replace(chr(0xE2) . chr(0x82) . chr(0xAC), "", $prefix);

        if ($prefix != " " && $prefix != "  " && $prefix != "  " && $prefix != "   ")
            $this->prefix = $prefix;
    }

    function data_style_set_suffix($suffix)
    {
        // Remove euro sign
        $suffix = str_replace(chr(0xE2) . chr(0x82) . chr(0xAC), "", $suffix);

        if ($suffix != " " && $suffix != "  " && $suffix != "  " && $suffix != "   ")
            $this->suffix = $suffix;
    }

    // Returns the format code of the data style
    function format_code()
    {
        $code = "";
        for ($i = 0; $i < $this->min_int_digit; $i++) {
            $code .= '0';
        }
        $is_first = true;
        for ($i = 0; $i < $this->decimal_places; $i++) {
            $code .= ($is_first) ? '.' : '';
            $code .= '0';
            $is_first = false;
        }

        return $this->prefix . $code . $this->suffix;
    }
}
