<?php

namespace Appizy;

class TableElement
{
    /** @var integer */
    var $eid;
    /** @var string[] */
    var $stylesNameList;

    function __construct($element_id)
    {
        $this->set_id($element_id);
        $this->stylesNameList = [];
    }

    function set_id($element_id)
    {
        $this->eid = (int)$element_id;
    }

    function getId() {
        return $this->eid;
    }

    function get_id()
    {
        return $this->eid;
    }

    /**
     * @param string $styleName
     */
    function addStyle($styleName)
    {
        $this->stylesNameList[] = $styleName;
    }

    /**
     * Returns styles name concatained with a $separator
     */
    function get_styles_name($separator = " ")
    {
        $styles_name = "";
        $is_first = true;
        foreach ($this->stylesNameList as $name) {
            $styles_name .= ($is_first) ? $name : " " . $name;
            $is_first = false;
        }

        return $styles_name;
    }

    /**
     * @return mixed
     */
    function getStyleName()
    {
        return array_shift($this->stylesNameList);
    }

    /**
     * @return array
     */
    function getStyles()
    {
        return $this->stylesNameList;
    }

    function get_styles()
    {
        return $this->stylesNameList;
    }
}
