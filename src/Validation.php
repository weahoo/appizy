<?php

namespace Appizy;

class Validation
{
    protected $cell_coord;

    protected $condition;

    /**
     * @return mixed
     */
    public function getCellCoord()
    {
        return $this->cell_coord;
    }

    /**
     * @param mixed $cell_coord
     */
    public function setCellCoord($cell_coord)
    {
        $this->cell_coord = $cell_coord;
    }

    /**
     * @return mixed
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @param mixed $condition
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;
    }
}
