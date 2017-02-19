<?php

namespace Appizy;

class Column extends TableElement
{
    /** @var bool */
    var $collapse;
    /** @var string */
    var $default_cell_style;

    function __construct($colid)
    {
        parent::__construct($colid);
        $this->default_cell_style = "";
        $this->collapse = false;
    }

    function setDefaultCellStyle($newStyle)
    {
        $this->default_cell_style = $newStyle;
    }

    function collapse()
    {
        $this->collapse = true;
    }

    function isCollapsed()
    {
        return $this->collapse == true;
    }

    function getDefaultCellStyle()
    {
        return $this->default_cell_style;
    }
}
