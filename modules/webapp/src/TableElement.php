<?php

namespace Appizy\WebApp;

class TableElement
{
    // Index of the TableElement
    var $eid;

    /** @var array */
    var $styles_name = array();

    function __construct($element_id)
    {
        $this->set_id($element_id);
        $this->styles_name = [];
    }

    function set_id($element_id)
    {
        $this->eid = (int) $element_id;
    }

    function tabelmt_error($message)
    {
        trigger_error(__CLASS__ . ': ' . $message, E_USER_WARNING);
    }

    function tabelmt_debug($message)
    {
        trigger_error(__CLASS__ . ': ' . $message);
    }

    function get_id()
    {
        return $this->eid;
    }

    function add_style_name($new_style_name)
    {
        $this->styles_name[] = $new_style_name;
    }

    /**
     * Returns styles name concatained with a $separator
     */
    function get_styles_name($separator = " ")
    {
        $styles_name = "";
        $is_first = true;
        foreach ($this->styles_name as $name) {
            $styles_name .= ($is_first) ? $name : " " . $name;
            $is_first = false;
        }

        return $styles_name;
    }

    /**
     * @return array
     */
    function getStyles() {
        return $this->styles_name;
    }

    function get_styles()
    {
        return $this->styles_name;
    }
}
