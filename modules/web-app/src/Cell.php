<?php

namespace Appizy\WebApp;

class Cell extends TableElement
{
    var $coord; // Coordonn�es de la cellule sheet,row,col - identifie de fa�on unique la cellule
    var $type; // Type de cellule pour Appizy : text, in, out
    var $value_type; // Type de valeur de la cellule : string, float, boolean
    var $value_attr; // Valeur de la cellule
    var $value_disp; // Valeur vue (il peut y avoir une diff�rence de mise en forme avec $value_attr)
    var $value_inlist; // Liste de l'ensemble des valeurs de la cellule, si vide valeur libre

    var $validation;

    var $formula; // Formule associ�e � la cellule. La formule est stock�e en langage javascript
// Colonnes et lignes fusionn�es
    var $colspan;
    var $rowspan;
// Nom du style (CSS) associ� � la cellule
    var $styles = array();
// Comment on the cell
    var $annotation;

    function __construct($sheet, $row, $col, $options = array())
    {
        $this->set_id($col);
//
        $this->coord = array('sheet' => $sheet,
                             'row'   => $row,
                             'col'   => $col
        );

        if (isset($options['style'])) $this->add_style_name($options['style']);

        $this->value_disp = isset($options['value_disp']) ?
            $options['value_disp'] : "";
        $this->value_attr = isset($options['value_attr']) ?
            $options['value_attr'] : "";
        $this->type = isset($options['type']) ?
            $options['type'] : "text";
        $this->value_type = isset($options['value_type']) ?
            $options['value_type'] : "string";
        $this->rowspan = isset($options['rowspan']) ?
            $options['rowspan'] : 1;
        $this->colspan = isset($options['colspan']) ?
            $options['colspan'] : 1;
        $this->validation = isset($options['validation']) ?
            $options['validation'] : null;
        $this->collapse = false;
        $this->value_inlist = array();
        $this->annotation = isset($options['annotation']) ?
            $options['annotation'] : '';

        if (isset($options['formula'])) {
            $this->formula = $options['formula'];
            $this->type = "out";
        }
    }

    /*
    Editeurs
    */
    function setValueType($myValueType)
    {
        $this->valueType = $myValueType;
    }

    function setValue($myValue)
    {
        $this->value_disp = $myValue;
    }

    function setValueAttr($myValueAttr)
    {
        $this->value_attr = $myValueAttr;
    }

    function cell_set_type($myType)
    {
        $this->type = $myType;
    }

    function addStyle($style_name = null)
    {
        $this->styles[] = $style_name;
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

    /**
     * Return cell displayed value
     */
    function cell_get_value_disp()
    {
        return $this->value_disp;
    }

    /**
     * Return cell attributed value
     */
    function cell_get_value_attr()
    {
        return $this->value_attr;
    }

    function getValueList()
    {
        return $this->value_inlist;
    }

    function cell_get_colspan()
    {
        return $this->colspan;
    }

    function cell_get_rowspan()
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

    function guessType()
    {
        if ($this->getFormula() != null) {
            $this->type = "out";
// Si formule dans la cellule, lexage de la formule pour deviner les interd�pendants

// On r�cup�re les cellules d�pendantes

// setType des d�pendants en input sauf si il y a d�j� une formule !
        }
    }

    function getFormula()
    {
        return $this->formula;
    }

    function setFormula($myFormula)
    {
        $this->formula = $myFormula;
    }

    function isFormula()
    {
        return ($this->getType() == "out");
    }

    function getType()
    {
        return $this->type;
    }

    function cell_isempty()
    {
        $empty = (
            $this->get_styles_name() == '' &&
            $this->cell_get_value() == '' &&
            $this->cell_get_validation() == '' &&
            $this->getType() != 'out'
        );

        return $empty;
    }

    /**
     * Return cell attribute value first or displayed value if not existent
     */
    function cell_get_value()
    {

        $cell_value = ($value_attr = $this->value_attr) ?
            $value_attr : $this->value_disp;

        if ($this->type == "in")
            $cell_value = strip_tags($cell_value);

        return $cell_value;
    }

    function cell_get_validation()
    {
        return $this->validation;
    }
}
