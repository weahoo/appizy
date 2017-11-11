<?php

namespace Appizy;

class Sheet extends TableElement
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var Column[]
     */
    protected $col;
    /**
     * @var Row[]
     */
    protected $row;

    public function __construct($sheetId, $sheetName)
    {
        parent::__construct($sheetId);

        $this->name = $sheetName;
        $this->col = [];
        $this->row = [];
    }

    public function addCol(Column $newCol)
    {
        $col_ind = $newCol->getId();
        $this->col[$col_ind] = $newCol;
    }

    /**
     * @param Row $newRow
     */
    public function addRow(Row $newRow)
    {
        $rowId = $newRow->getId();
        $this->row[$rowId] = $newRow;
    }

    /**
     * @param $columnId
     * @return Column
     * @throws \Exception
     */
    public function getCol($columnId)
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
    public function getColumns()
    {
        return $this->col;
    }

    /**
     * @return Row[]
     */
    public function getRows()
    {
        return $this->row;
    }

    /**
     * @param $rowId
     * @return Row|bool
     */
    public function getRow($rowId)
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
    public function setRows($newRows)
    {
        $this->row = $newRows;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function removeEmptyRows()
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

    public function isEmpty()
    {
        $rows = $this->getRows();

        return empty($rows);
    }
}
