<?php

namespace Appizy\WebApp;

class Formula
{
    var $cell_coord; // coordonn�es de la cellule d'origine
    var $formula_elements;
    var $dependances; // Tableau des coordonn�es de cellules dont d�pent la formule
    var $ext_formula_dependances; // Liste des fonctions externes dont d�pend la formule
    var $error;

    function __construct()
    {
        $this->error = [];
    }

    function getCall()
    {
        return $this->getName() . '();';
    }

    function getName()
    {
        $coord = $this->cell_coord;

        return 's' . $coord[0] . 'r' . $coord[1] . 'c' . $coord[2];
    }

    function getDependencies()
    {
        return $this->dependances;
    }

    function getExternalFormulas()
    {
        return $this->ext_formula_dependances;
    }

    function getScript()
    {
        $formula_name = $this->getName();
        $script = "";
        foreach ($this->formula_elements as $formula_element) {
            $script .= $formula_element;
        }

        return "function " . $formula_name . "(){ APY.set('" . $formula_name . "'," . $script . ") }";
    }

    /**
     * @return boolean
     */
    function isPrintable()
    {
        return count($this->error) === 0;
    }
}
