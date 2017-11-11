<?php

namespace Appizy;

class Row extends TableElement
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var  int
     */
    protected $sheet_ind;
    /**
     * @var  int
     */
    protected $row_ind;
    /**
     * @var  boolean
     */
    protected $collapse;
    /**
     * @var Cell[]
     */
    protected $cells;

    /**
     * Row constructor.
     *
     * @param int   $sheet_ind
     * @param int   $row_ind
     * @param array $options
     */
    public function __construct($sheet_ind, $row_ind, $options)
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
    public function addCell(Cell $newCell)
    {
        $cell_id = $newCell->getId();
        $this->cells[$cell_id] = $newCell;
    }

    /**
     * @return array
     */
    public function getStyles()
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
    public function isHidden()
    {
        return $this->collapse === true;
    }

    /**
     * @param $cellId
     * @return Cell|Bool
     */
    public function getCell($cellId)
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
    public function getName()
    {
        return $this->name;
    }

    public function cleanRow()
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
    public function getCells()
    {
        return $this->cells;
    }

    /**
     * @param Cell[] $newCells
     */
    public function setCells($newCells)
    {
        $this->cells = $newCells;
    }

    public function isEmptyRow()
    {
        $cells = $this->getCells();

        return empty($cells);
    }

    public function collapseRow()
    {
        $this->collapse = true;
    }
}
