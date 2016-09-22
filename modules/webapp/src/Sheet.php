<?php

namespace Appizy\WebApp;

class Sheet extends TableElement
{
    /** @var  string */
    var $name;
    /** @var Column[] */
    var $col;
    /** @var Row[] */
    var $row;

    function __construct($sheet_id, $sheet_name)
    {
        parent::__construct($sheet_id);

        $this->name = $sheet_name;
        $this->col = [];
        $this->row = [];
    }

    function addCol(Column $newCol)
    {
        $col_ind = $newCol->get_id();
        $this->col[$col_ind] = $newCol;
    }

    /**
     * @param Row $newRow
     */
    function addRow(Row $newRow)
    {
        $rowId = $newRow->getId();
        $this->row[$rowId] = $newRow;
    }

    function getCol($col_key)
    {
        $column = false;
        if (array_key_exists($col_key, $this->col)) {
            $column = $this->col[$col_key];
        }

        return $column;
    }

    function getColumns()
    {
        return $this->col;
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

    /**
     * @param $rowId
     * @return Row
     * @throws \Exception
     */
    function getRow($rowId)
    {
        $rows = $this->row;

        if (array_key_exists($rowId, $rows)) {
            $row = $rows[$rowId];
        } else {
            throw new \Exception("Row $rowId does not exist,");
        }

        return $row;
    }

    function sheet_get_cell($row_ind, $col_ind)
    {
        $cell = false;

        $row = $this->getRow($row_ind);

        if ($row) {

            $cell = $row->getCell($col_ind);
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
