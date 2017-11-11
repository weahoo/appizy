<?php

namespace Appizy;

class Column extends TableElement
{
    /** @var bool */
    protected $collapse;
    /** @var string */
    protected $default_cell_style;

    public function __construct($colid)
    {
        parent::__construct($colid);
        $this->default_cell_style = "";
        $this->collapse = false;
    }

    public function setDefaultCellStyle($newStyle)
    {
        $this->default_cell_style = $newStyle;
    }

    public function collapse()
    {
        $this->collapse = true;
    }

    public function isCollapsed()
    {
        return $this->collapse == true;
    }

    public function getDefaultCellStyle()
    {
        return $this->default_cell_style;
    }
}
