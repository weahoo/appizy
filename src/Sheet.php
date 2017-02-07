<?php

namespace Appizy;

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
     * @return Row|bool
     */
    function getRow($rowId)
    {
        $row = false;

        if (array_key_exists($rowId, $this->row)) {
            $row = $this->row[$rowId];
        }

        return $row;
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
