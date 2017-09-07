<?php

namespace Appizy;

class Row extends TableElement
{
    /** @var string */
    var $name;
    /** @var  int */
    var $sheet_ind;
    /** @var  int */
    var $row_ind;
    /** @var  boolean */
    var $collapse;
    /** @var Cell[] */
    var $cells;

    /**
     * Row constructor.
     * @param int $sheet_ind
     * @param int $row_ind
     * @param array $options
     */
    function __construct($sheet_ind, $row_ind, $options)
    {
        parent::__construct($row_ind);

        $this->sheet_ind = $sheet_ind;
        $this->name = 's' . $sheet_ind . 'r' . $row_ind;
        $this->cells = [];

        if (isset($options['collapse'])) {
            $this->collapse = $options['collapse'];
        }

        if (isset($options['style'])) {
            $this->addStyle($options['style']);
        }
    }

    /**
     * @param Cell $newCell
     */
    function addCell(Cell $newCell)
    {
        $cell_id = $newCell->get_id();
        $this->cells[$cell_id] = $newCell;
    }

    /**
     * @return array
     */
    function getStyles()
    {
        $styles = parent::getStyles();
        if ($this->isHidden()) {
            $styles[] = 'hidden-row';
        }

        return $styles;
    }

    /**
     * @return bool
     */
    function isHidden()
    {
        return $this->collapse === true;
    }

    /**
     * @param $cellId
     * @return Cell|Bool
     */
    function getCell($cellId)
    {
        if (array_key_exists($cellId, $this->cells)) {
            return $this->cells[$cellId];
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    function getName()
    {
        return $this->name;
    }

    function cleanRow()
    {
        $isFirstFilled = false;
        $offset = 0;
        $reservedCells = array_reverse($this->getCells(), true);

        /** @var Cell $tempCell */
        foreach ($reservedCells as $tempCell) {
            if (!$isFirstFilled) {
                if ($tempCell->isEmpty()) {
                    $offset++;
                } else {
                    $isFirstFilled = true;
                }
            }
        }

        if ($offset > 0) {
            $reservedCells = array_slice($reservedCells, $offset);
        }

        $cells = array_reverse($reservedCells, true);
        $this->setCells($cells);
    }

    /**
     * @return Cell[]
     */
    function getCells()
    {
        return $this->cells;
    }

    /**
     * @param Cell[] $newCells
     */
    function setCells($newCells)
    {
        $this->cells = $newCells;
    }

    function isEmptyRow()
    {
        $cells = $this->getCells();

        return empty($cells);
    }

    function collapseRow()
    {
        $this->collapse = true;
    }
}

