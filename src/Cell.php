<?php

namespace Appizy;

use Appizy\Constant\CellAttributes;

class Cell extends TableElement
{
    protected $coord; // Coordonn�es de la cellule sheet,row,col - identifie de fa�on unique la cellule
    protected $value_type; // Type de valeur de la cellule : string, float, boolean
    protected $value_inlist; // Liste de l'ensemble des valeurs de la cellule, si vide valeur libre
    protected $validation;

    /**
     * @var string
     */
    protected $type; // Type de cellule pour Appizy : text, in, out
    /**
     * @var string
     */
    protected $value;
    /**
     * @var string
     */
    protected $displayedValue;
    /**
     * @var int
     */
    protected $colspan;
    /**
     * @var int
     */
    protected $rowspan;
    /**
     * @var string[]
     */
    protected $styles;
    /**
     * @var string
     */
    protected $annotation;
    /**
     * @var bool
     */
    protected $collapse;
    /**
     * @var string
     */
    protected $valueType;

    public function __construct($sheet, $row, $col, $options = array())
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

    public function setValueType($myValueType)
    {
        $this->valueType = $myValueType;
    }

    public function setDisplayedValue($myValue)
    {
        $this->displayedValue = $myValue;
    }

    public function setValueAttr($myValueAttr)
    {
        $this->value = $myValueAttr;
    }

    /**
     * @param string $myType
     */
    public function setType($myType)
    {
        $this->type = $myType;
    }

    public function setValueInList($myList)
    {
        $this->value_inlist = $myList;
    }

    public function getAnnotation()
    {
        $annotation = $this->annotation;

        // Just gets the content inside p tags.
        if ($annotation) {
            preg_match('/\>(.*)<\/p>/', $annotation, $matches);
            $annotation = strip_tags($matches[1]);
        }

        return $annotation;
    }

    /**
     * @return string
     */
    public function getName()
    {
        $name = 's' . $this->coord['sheet'] . 'r' . $this->coord['row'] . 'c' . $this->coord['col'];

        return $name;
    }

    /**
     * Return cell displayed value
     */
    public function getDisplayedValue()
    {
        return $this->displayedValue;
    }

    /**
     * Return cell attribute value first or displayed value if not existent
     */
    public function getValue()
    {
        if (isset($this->value)) {
            $cellValue = $this->value;
        } else {
            $cellValue = $this->displayedValue;
        }

        if ($this->type === CellAttributes::TYPE_INPUT) {
            $cellValue = strip_tags($cellValue);
        }

        return html_entity_decode($cellValue);
    }

    public function getValueAttr()
    {
        return $this->value;
    }

    public function getValueList()
    {
        return $this->value_inlist;
    }

    public function getColSpan()
    {
        return $this->colspan;
    }

    public function getRowSpan()
    {
        return $this->rowspan;
    }

    public function getStyle()
    {
        $style = "";
        $is_first = true;
        foreach ($this->styles as $style_name) {
            $style .= ($is_first) ? $style_name : " " . $style_name;
            $is_first = false;
        }

        return $style;
    }

    public function collapseCell()
    {
        $this->collapse = true;
    }

    public function isFormula()
    {
        return ($this->getType() == CellAttributes::TYPE_OUTPUT);
    }

    public function getType()
    {
        return $this->type;
    }

    public function getValueType()
    {
        return $this->value_type;
    }

    public function isEmpty()
    {
        $empty = (
            $this->getConcatStyleNames() == '' &&
            $this->getValue() == '' &&
            $this->getValidation() == '' &&
            $this->getType() != CellAttributes::TYPE_OUTPUT
        );

        return $empty;
    }

    public function getValidation()
    {
        return $this->validation;
    }
}
