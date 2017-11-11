<?php

namespace Appizy;

class Formula
{
    protected $cell_coord; // coordonn�es de la cellule d'origine
    protected $formula_elements;
    protected $dependances; // Tableau des coordonn�es de cellules dont d�pent la formule
    protected $ext_formula_dependances; // Liste des fonctions externes dont d�pend la formule
    protected $error;

    public function __construct()
    {
        $this->error = [];
        $this->formula_elements = [];
    }

    /**
     * @return mixed
     */
    public function getDependances()
    {
        return $this->dependances;
    }

    /**
     * @param mixed $dependances
     */
    public function setDependances($dependances)
    {
        $this->dependances = $dependances;
    }

    /**
     * @return mixed
     */
    public function getExtFormulaDependances()
    {
        return $this->ext_formula_dependances;
    }

    /**
     * @param mixed $ext_formula_dependances
     */
    public function setExtFormulaDependances($ext_formula_dependances)
    {
        $this->ext_formula_dependances = $ext_formula_dependances;
    }

    /**
     * @return string
     */
    public function getCall()
    {
        return $this->getName() . '();';
    }

    /**
     * @return array
     */
    public function getElements()
    {
        return $this->formula_elements;
    }

    /**
     * @param string[] $elements
     */
    public function setElements($elements)
    {
        $this->formula_elements = $elements;
    }


    /**
     * @return mixed
     */
    public function getCellCoord()
    {
        return $this->cell_coord;
    }

    /**
     * @param mixed $cell_coord
     */
    public function setCellCoord($cell_coord)
    {
        $this->cell_coord = $cell_coord;
    }

    /**
     * @return string
     */
    public function getName()
    {
        $coord = $this->cell_coord;

        return 's' . $coord[0] . 'r' . $coord[1] . 'c' . $coord[2];
    }

    /**
     * @return mixed
     */
    public function getDependencies()
    {
        return $this->dependances;
    }

    /**
     * @return mixed
     */
    public function getExternalFormulas()
    {
        return $this->ext_formula_dependances;
    }

    /**
     * @return string
     */
    public function getScript()
    {
        $name = $this->getName();
        $script = join('', $this->getElements());

        return "APY.formulas." . $name . " = function() { APY.set('" . $name . "'," . $script . ") }";
    }

    /**
     * @return boolean
     */
    public function isPrintable()
    {
        return count($this->error) === 0;
    }
}
