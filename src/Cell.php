<?php

namespace Appizy;

use Appizy\Constant\CellAttributes;

class Cell extends TableElement
{
    var $coord; // Coordonn�es de la cellule sheet,row,col - identifie de fa�on unique la cellule
    var $value_type; // Type de valeur de la cellule : string, float, boolean
    var $value_inlist; // Liste de l'ensemble des valeurs de la cellule, si vide valeur libre
    var $validation;

    /** @var string */
    var $type; // Type de cellule pour Appizy : text, in, out
    /** @var string */
    var $value;
    /** @var string */
    var $displayedValue;
    /** @var int */
    var $colspan;
    /** @var int */
    var $rowspan;
    /** @var string[] */
    var $styles;
    /** @var string */
    var $annotation;
    /** @var bool */
    var $collapse;
    /** @var string */
    var $valueType;

    function __construct($sheet, $row, $col, $options = array())
    {
        parent::__construct($col);

        $this->coord = array(
            'sheet' => $sheet,
            'row' => $row,
            'col' => $col
        );

        if (isset($options['style'])) {
            $this->addStyle($options['style']);
        }

        if (isset($options['value_disp'])) {
            $this->setDisplayedValue($options['value_disp']);
        }

        if (isset($options['value_attr'])) {
            $this->setValueAttr($options['value_attr']);
        }

        $this->type = isset($options['type']) ?
            $options['type'] : "text";
        $this->value_type = isset($options['value_type']) ?
            $options['value_type'] : CellAttributes::VALUE_TYPE_STRING;
        $this->rowspan = (int)isset($options['rowspan']) ?
            $options['rowspan'] : CellAttributes::DEFAULT_ROW_SPAN;
        $this->colspan = (int)isset($options['colspan']) ?
            $options['colspan'] : CellAttributes::DEFAULT_COL_SPAN;
        $this->validation = isset($options['validation']) ?
            $options['validation'] : null;
        $this->collapse = false;
        $this->value_inlist = array();
        $this->annotation = isset($options['annotation']) ?
            $options['annotation'] : '';

        if (isset($options['formula'])) {
            $this->formula = $options['formula'];
            $this->type = CellAttributes::TYPE_OUTPUT;
        }
    }

    function setValueType($myValueType)
    {
        $this->valueType = $myValueType;
    }

    function setDisplayedValue($myValue)
    {
        $this->displayedValue = $myValue;
    }

    function setValueAttr($myValueAttr)
    {
        $this->value = $myValueAttr;
    }

    function cell_set_type($myType)
    {
        $this->type = $myType;
    }

    function setValueInList($myList)
    {
        $this->value_inlist = $myList;
    }

    function cell_get_annotation()
    {
        $annotation = $this->annotation;

        // Just gets the content inside p tags.
        if ($annotation) {
            preg_match('/\>(.*)<\/p>/', $annotation, $matches);
            $annotation = strip_tags($matches[1]);
        }

        return $annotation;
    }

    function getName()
    {
        $name = 's' . $this->coord['sheet'] . 'r' . $this->coord['row'] . 'c' . $this->coord['col'];

        return $name;
    }

    function cell_value_type()
    {
        return $this->value_type;
    }

    function getDisplayedValue()
    {
        return $this->displayedValue;
    }

    /**
     * Return cell displayed value
     */
    function cell_get_value_disp()
    {
        return $this->displayedValue;
    }

    /**
     * Return cell attribute value first or displayed value if not existent
     */
    function getValue()
    {
        if (isset($this->value)) {
            $cellValue = $this->value;
        } else {
            $cellValue = $this->displayedValue;
        }

        if ($this->type === CellAttributes::TYPE_INPUT) {
            $cellValue = strip_tags($cellValue);
        }

        return $cellValue;
    }

    function getValueAttr()
    {
        return $this->value;
    }

    function getValueList()
    {
        return $this->value_inlist;
    }

    function getColSpan()
    {
        return $this->colspan;
    }

    function getRowSpan()
    {
        return $this->rowspan;
    }

    function getStyle()
    {
        $style = "";
        $is_first = true;
        foreach ($this->styles as $style_name) {
            $style .= ($is_first) ? $style_name : " " . $style_name;
            $is_first = false;
        }

        return $style;
    }

    function collapseCell()
    {
        $this->collapse = true;
    }

    function isFormula()
    {
        return ($this->getType() == CellAttributes::TYPE_OUTPUT);
    }

    function getType()
    {
        return $this->type;
    }

    function getValueType()
    {
        return $this->value_type;
    }

    function isEmpty()
    {
        $empty = (
            $this->get_styles_name() == '' &&
            $this->getValue() == '' &&
            $this->cell_get_validation() == '' &&
            $this->getType() != CellAttributes::TYPE_OUTPUT
        );

        return $empty;
    }

    function cell_get_validation()
    {
        return $this->validation;
    }
}
