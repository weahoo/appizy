<?php

namespace Appizy\WebApp;

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

    function col_set_default_cell_style($newStyle)
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

    function col_get_default_cell_style()
    {
        return $this->default_cell_style;
    }
}
