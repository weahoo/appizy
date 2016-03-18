<?php

namespace Appizy\WebApp;

use Appizy\WebApp\TableElement;

class Row extends TableElement
{
    var $name;
    var $sheet_ind;
    var $row_ind;
    var $cells;
    var $collapse;

    function __construct($sheet_ind, $row_ind, $options)
    {
        $this->set_id($row_ind);

        $this->sheet_ind = $sheet_ind;
        $this->row_ind = $row_ind;
        $this->name = 's' . $sheet_ind . 'r' . $row_ind;

        $this->cell = [];

        if (isset($options['collapse'])) $this->collapse = $options['collapse'];
        if (isset($options['style'])) $this->add_style_name($options['style']);
    }

    function addCell(Cell $newCell)
    {
        $cell_id = $newCell->get_id();
        $this->cell[$cell_id] = $newCell;
    }

    function getCells()
    {
        return $this->cell;
    }

    function row_get_cells()
    {
        return $this->cell;
    }

    function row_get_cell($cell_ind)
    {
        if (array_key_exists($cell_ind, $this->cell)) {
            return $this->cell[$cell_ind];
        } else {
            $this->tabelmt_debug("Unexistent cell r" . $this->get_id() . "c$cell_ind");

            return false;
        }
    }

    /**
     * Returns the number of cells in a row
     */
    function row_nbcell()
    {
        return count($this->row_get_cells());
    }

    /**
     * Returns the size of a row = nb of cells + colspan
     */
    function row_length()
    {
        $length = 0;
        $cells = $this->row_get_cells();

        foreach ($cells as $cell) {
            $rowspan = $cell->cell_get_colspan();
            $length += $rowspan;
        }

        return $length;
    }

    function get_rowind()
    {
        return $this->get_id();
    }

    function get_cell($cell_ind)
    {
        return $this->cell[$cell_ind];
    }

    function getName()
    {
        return $this->name;
    }

    function getStyle()
    {
        return $this->style;
    }

    function row_set_cell($new_cells)
    {
        $this->cell = $new_cells;
    }

    function cleanRow()
    {

        $isFirstFilled = false;
        $offset = 0;
        // On inverse les cells
        $cells_reverse = array_reverse($this->getCells(), true);

        // On nettoie ensuite chaque row
        foreach ($cells_reverse as $tempcell) {
            if (!$isFirstFilled) :
                if ($tempcell->cell_isempty()) {
                    $offset++;
                } else {
                    $isFirstFilled = true;
                }
            endif;
        }
        // On supprime les $offset premi�res $sheet vides
        if ($offset > 0) $cells_reverse = array_slice($cells_reverse, $offset);
        // On inverse a nouveau et on affecte les sheets du tableau
        $cells = array_reverse($cells_reverse, true);
        $this->row_set_cell($cells);

    }

    function isEmptyRow()
    {
        $cells = $this->getCells();

        return empty($cells);
    }

    function cellExistInRows($coord = array())
    {
        return array_key_exists($coord['cell'], $this->getCells());
    }

    function collapseRow()
    {
        $this->collapse = true;
    }
}

