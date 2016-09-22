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

    function __construct($sheetId, $sheetName)
    {
        parent::__construct($sheetId);

        $this->name = $sheetName;
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

    /**
     * @param $columnId
     * @return Column
     * @throws \Exception
     */
    function getCol($columnId)
    {
        if (array_key_exists($columnId, $this->col)) {
            $column = $this->col[$columnId];
        } else {
            throw new \Exception("Column $columnId does not exists in sheet");
        }

        return $column;
    }

    /**
     * @return Column[]
     */
    function getColumns()
    {
        return $this->col;
    }

    /**
     * @return Row[]
     */
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

    /**
     * @param Row[] $newRows
     */
    function setRows($newRows)
    {
        $this->row = $newRows;
    }

    /**
     * @return string
     */
    function getName()
    {
        return $this->name;
    }

    function removeEmptyRows()
    {
        $isFirstFilled = false;
        $offset = 0;
        $rows = $this->getRows();
        $row_nb = count($rows);
        $reversedRows = array_reverse($rows, true);

        /** @var Row $tempRow */
        foreach ($reversedRows as $tempRow) {

            $tempRow->cleanRow();

            if (!$isFirstFilled) {
                if ($tempRow->isEmptyRow()) {
                    $offset++;
                } else {
                    $isFirstFilled = true;
                }
            }

        }

        // On supprime les $offset premi�res $sheet vides
        if ($offset > 0) {
            $rows = array_slice($rows, 0, $row_nb - $offset);
        }

        $this->setRows($rows);
    }

    function isEmptySheet()
    {
        $rows = $this->getRows();

        return empty($rows);
    }
}
