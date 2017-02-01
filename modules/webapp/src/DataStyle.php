<?php

namespace Appizy\WebApp;

class DataStyle
{
    var $id;
    /** @var int */
    var $decimalPlaces;
    /** @var int */
    var $minIntDigit;
    var $maps;
    /** @var string */
    var $prefix;
    /** @var string */
    var $suffix;
    /** @var boolean */
    var $grouping;

    function __construct($id)
    {
        $this->id = $id;
        $this->decimalPlaces = 0;
    }

    function setPrefix($prefix)
    {
        // Remove euro sign
        $prefix = str_replace(chr(0xE2) . chr(0x82) . chr(0xAC), "", $prefix);

        if ($prefix != " " && $prefix != "  " && $prefix != "  " && $prefix != "   ") {
            $this->prefix = $prefix;
        }
    }

    function setSuffix($suffix)
    {
        // Remove euro sign
        $suffix = str_replace(chr(0xE2) . chr(0x82) . chr(0xAC), "", $suffix);

        if ($suffix != " " && $suffix != "  " && $suffix != "  " && $suffix != "   ") {
            $this->suffix = $suffix;
        }
    }

    /**
     * @return string
     */
    function toNumeralStringFormat()
    {
        $code = '0';

        if ($this->grouping) {
            $code .= ',0';
        }

        $is_first = true;
        for ($i = 0; $i < $this->decimalPlaces; $i++) {
            $code .= ($is_first) ? '.' : '';
            $code .= '0';
            $is_first = false;
        }

        return $this->prefix . $code . $this->suffix;
    }
}
