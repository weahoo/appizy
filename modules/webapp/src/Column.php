<?php

namespace Appizy\WebApp;

class Column extends TableElement
{
    var $colid;
    var $collapse;
    var $default_cell_style;

    function __construct($colid)
    {
        $this->default_cell_style = "";
        $this->collapse = false;
    }

    function get_colid()
    {
        return $this->get_id();
    }

    function col_set_default_cell_style($newStyle)
    {
        $this->default_cell_style = $newStyle;
    }

    function col_collapse()
    {
        $this->collapse = true;
    }

    function get_collapsed()
    {
        return $this->collapse == true;
    }

    function col_get_default_cell_style()
    {
        return $this->default_cell_style;
    }
}
