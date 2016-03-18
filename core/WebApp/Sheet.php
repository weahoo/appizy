<?php

namespace Appizy\WebApp;

use Appizy\WebApp\Row;
use Appizy\WebApp\Column;
use Appizy\WebApp\Cell;

class Sheet extends TableElement
{
    var $name;
    var $style;
    var $col;
    var $row;

    function __construct($sheet_id, $sheet_name)
    {
        $this->set_id($sheet_id);
        //
        $this->name = $sheet_name;
        $this->style = "";
        $this->col = array();
        $this->row = array();
    }

    function addCol(Column $newCol)
    {
        $col_ind = $newCol->get_colid();
        $this->col[$col_ind] = $newCol;
    }

    function addRow(Row $newRow)
    {
        $row_ind = $newRow->get_rowind();
        $this->row[$row_ind] = $newRow;
    }

    function getCol($col_key)
    {
        $column = false;
        if (array_key_exists($col_key, $this->col)) {
            $column = $this->col[$col_key];
        }

        return $column;
    }

    /**
     * Returns sheet cols
     */
    function sheet_get_cols()
    {
        return $this->col;
    }

    function getRows()
    {
        return $this->row;
    }

    function getRow($key_row)
    {
        return $this->row[$key_row];
    }

    function sheet_get_row($row_ind)
    {
        $rows = $this->row;
        $row = false;
        if (array_key_exists($row_ind, $rows)) {
            $row = $rows[$row_ind];
        } else {
            $this->tabelmt_debug("Unexistent row: s" . $this->get_id() . "r$row_ind");
        }

        return $row;
    }

    function sheet_get_cell($row_ind, $col_ind)
    {
        $cell = false;

        $row = $this->sheet_get_row($row_ind);

        if ($row) {

            $cell = $row->row_get_cell($col_ind);
            if (!$cell) {
                $this->tabelmt_debug("Unexistent cell: s" . $this->get_id() . "r$row_ind" . "c$col_ind");
            }
        }

        return $cell;
    }

    function setRows($new_rows)
    {
        $this->row = $new_rows;
    }

    function get_sheet_name()
    {
        return $this->name;
    }

    function getName()
    {
        return $this->name;
    }

    function sheet_clean()
    {

        $isFirstFilled = false;
        $offset = 0;
        // On inverse les rows
        $rows = $this->getRows();
        $row_nb = count($rows);

        $rows_reverse = array_reverse($rows, true);

        // On nettoie ensuite chaque row
        foreach ($rows_reverse as $temprow) {

            $temprow->cleanRow();

            if (!$isFirstFilled) {
                if ($temprow->isEmptyRow()) {
                    $offset++;
                } else {
                    $isFirstFilled = true;
                }
            }

        }
        // $this->tabelmt_debug("Clean sheet s".$this->get_id()."offset:$offset");

        // On supprime les $offset premi�res $sheet vides
        if ($offset > 0) $rows = array_slice($rows, 0, $row_nb - $offset);

        $this->setRows($rows);
    }

    function isEmptySheet()
    {
        $rows = $this->getRows();

        return empty($rows);
    }

    function cellExistsInSheet($coord = array())
    {
        $key_row = $coord['row'];
        if (array_key_exists($key_row, $this->getRows())) {
            $row = $this->getRows($key_row);

            return $row->cellExistInRow(array_slice($coord, 1));
        } else {
            return false;
        }
    }
}
