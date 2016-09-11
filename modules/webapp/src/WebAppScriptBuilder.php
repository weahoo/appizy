<?php

namespace Appizy\WebApp;


class WebAppScriptBuilder
{
    /** @var Tool */
    var $spreadSheet;

    /** @var  string[] */
    var $loadedFunction;

    /** @var  string[] */
    var $externalJsLibraries;

    function __construct(Tool $spreadSheet)
    {
        $this->loadedFunction = [];
        $this->externalJsLibraries = [];
        $this->spreadSheet = $spreadSheet;
    }

    function getExternalJsLibraries() {
        return $this->externalJsLibraries;
    }
    
    function buildScript()
    {
        $formulas = "// Cells formulas" . "\n";
        $formulaslist = [];
        $script = '';
        $steps = [];
        $ext_formulas = [];

        /** @var Formula $formula */
        foreach ($this->spreadSheet->formulas as $formula) {
            $dependances = array();
            foreach ($formula->getDependencies() as $dependance) {
                $dependances[] = 's' . $dependance[0] . 'r' . $dependance[1] . 'c' . $dependance[2];
            }

            $formulas .= $formula->getScript() . "\n";
            $formulaslist[$formula->getName()] = [
                'call' => $formula->getCall(),
                'dep' => $dependances,
            ];

            foreach ($formula->getExternalFormulas() as $ext_formula) {
                $ext_formulas[] = $ext_formula;
            }
        }
        $ext_formulas = array_unique($ext_formulas);

        $formulaslist_copy = $formulaslist; // $formulalist est copi� car nous allons avoir besoin de triturer cet Array
        $currentstep = 0; // Step de calcul en cours
        $fomulas_unclassified = count($formulaslist); // D�compte de formules qu'il reste � classer

        $fomulas_unclassified_laststep = 0; // Variable de sortie de la boucle while. Si jamais la boucle n'�limine pas de formule � la step...


        while (($fomulas_unclassified > 0) && ($fomulas_unclassified_laststep != $fomulas_unclassified)) {
            // Pour �viter que la boucle ne tourne dans le vide
            $fomulas_unclassified_laststep = $fomulas_unclassified;

            // Tant qu'il reste des formules � classer

            $steps[$currentstep]['formulas'] = array();
            $steps[$currentstep]['dep'] = array();

            // 1�re �tape - supprimer les d�pendances "r�solues"
            //
            // Les d�pendances de chaque formule sont pass�s en revue
            // Si la d�pendance n'est pas dans la liste des formules, cad :
            //     - la d�pendance est soit une formule d�j� calcul�e (elle a �t� supprim�e dans une boucle pr�c�dente)
            //     - la d�pendance est un input qui n'a jamais appartenu � la liste des formules
            // Alors la d�pendance est supprim�e de l'Array

            foreach ($formulaslist_copy as $formcell => $forminfo) {
                // Pour chaque formule dans la liste temporaire $formulalist_copy

                $offsetdep = 0; // Variable locale; index de la d�pendance � enlever au besoin !

                $nbdep = count($forminfo['dep']);
                // appizy_logapp("J'ai $nbdep dep");
                foreach ($forminfo['dep'] as $value) {
                    // Pour chaque d�pendance de la formule
                    if (!array_key_exists($value, $formulaslist_copy)) {
                        /*
                         *
                         *
                         */

                        array_splice($formulaslist_copy[$formcell]['dep'],
                            $offsetdep, 1);
                        // appizy_logapp("Dep calculee $value");
                    } else {
                        /*
                         * La formule d�pend d'une cellule qui fait partie de la liste des formules � calculer
                         * On incr�mente alors l'offset de d�pendance. La formule sera calcul�e � l'�tape n+1
                         */
                        //echo $formcell."Offsetdep:".$offsetdep."-Maxdep:".$nbdep."<br>";
                        $offsetdep++;
                    }
                }
            }

            $offsetform = 0; // Variable locale, index de la formule � enlever du tableau au besoin

            // 2�me �tape - ajouter les formules sans d�pendance � la step de calcul en cours

            foreach ($formulaslist_copy as $formula_index => $temp_formula) {
                // Pour chaque formule dans la liste temporaire $formulalist_copy

                if (count($temp_formula['dep']) == 0) {
                    // S'il n'y a plus de cellule non calcul�e dont d�pend la formule
                    // elle entre dans la step de calcul
                    // on la retire de la liste de cellule
                    array_push($steps[$currentstep]['formulas'],
                        $temp_formula['call']);

                    foreach ($formulaslist[$formula_index]['dep'] as $temp_dep) {
                        // On charge grace � l'original de la liste des formules
                        // l'ensemble des d�pendances de la formule "libre"
                        array_push($steps[$currentstep]['dep'], $temp_dep);
                    }

                    // La formule "libre" de tout d�pendance est supprim�e de la copie
                    array_splice($formulaslist_copy, $offsetform, 1);

                } else {

                    $offsetform++;
                }
            }

            // 3�me �tape - pr�parer la suite de l'algo
            // Le nombre de formule � classer est mis � jour
            // L'�tape en cours est incr�ment�e

            $fomulas_unclassified = count($formulaslist_copy);

            $currentstep++;

            // appizy_logapp("Number of formulas left:".$fomulas_unclassified." - step:".($currentstep-1)); // On loggue avant la mise � jour.
            foreach ($steps[$currentstep - 1]['formulas'] as $temp_formula) {
                // appizy_logapp($temp_formula) ;
            }

        }


        // L'Array des d�pendances est applanit avant d'�tre utilis� par la suite
        foreach ($steps as $currentstep) {
            $flat_stepdep = array();
            array_walk_recursive($currentstep['dep'],
                function ($a) use (&$flat_stepdep) {
                    $flat_stepdep[] = $a;
                });
        }

        // Impression des steps de calcul, uniquement s'il y a des �tapes de calcul
        if ($currentstep > 0) {
            $run_calc = 'function run_calc(){ ';
            $formulascall = '';
            $isFirstInput = true;

            foreach ($steps as $currentstep_index => $currentstep) {
                $stepdep = '';

                if (!$isFirstInput) : $run_calc .= ";";
                else : $isFirstInput = false; endif;

                $run_calc .= 'step' . $currentstep_index . '()';

                $formulascall .= 'function step' . $currentstep_index . '() {' . "\n" . "  ";

                foreach ($currentstep['formulas'] as $formula) {
                    $formulascall .= $formula;
                }
                $formulascall .= "\n" . '}' . "\n";
            }
        } else {
            $run_calc = "";
            $formulascall = "";
        }
        $run_calc .= "}" . "\n";

        if (!empty($formulascall)) {
            $script .= "(function() {" . "\n";

            $formulas_ext = "var root = this;" . "\n";
            $formulas_ext .= "var APY = root.APY = {};" . "\n";

            $accessFormulas = [
                'window.onload',
                '$.fn.setFormattedValue',
                'APY.getInput',
                'APY.set',
                'APY.formatValue',
                'window.RANGE'
            ];


            foreach ($accessFormulas as $formula) {
                $formulas_ext .= $this->getExtFunction($formula,
                    __DIR__ . "/../assets/js/src/appizy.js");
            }

            foreach ($ext_formulas as $ext_formula) {
                $formulas_ext .= $this->getExtFunction($ext_formula,
                    __DIR__ . "/../assets/js/src/formula-addons.js");
            }


            $script .= $run_calc;
            $script .= $formulascall;
            $script .= $formulas;
            $script .= $formulas_ext;
            $script .= "}).call();" . "\n";
        }

        return $script;
    }

    /**
     * @param string $function_name
     * @param string $library_path
     * @return string
     */
    private function getExtFunction($function_name, $library_path)
    {
        $externalFormulaScript = '';
        $namu = $function_name;
        $function_name = preg_quote($function_name);

        $formulaRegex = '/' . $function_name . ' = function(.*?)\};/is';

        if (!in_array($function_name, $this->loadedFunction)) {

            if (preg_match_all($formulaRegex, file_get_contents($library_path), $match)) {
                $function = $match[1][0];
                $externalFormulaScript = $namu . " = function" . $function . "};" . "\n\n";


                $this->loadedFunction[] = $namu;

                if (preg_match_all('/Formula.(.*?)\(/is', $function, $match)) {
                    // The current function depends on other function (having the form 'Formula.XYZ')
                    $match = array_unique($match[1]);
                    $match = array_diff($match, $this->loadedFunction);
                    foreach ($match as $dep_name) {

                        $dep_name = "Formula." . $dep_name;

                        if (!in_array($dep_name, $this->loadedFunction)) {
                            $externalFormulaScript .= $this->getExtFunction($dep_name,
                                $library_path, $this->loadedFunction);
                        }
                    }
                }

                if (preg_match_all('/jStat.(.*?)\(/is', $function, $match)) {
                    $this->externalJsLibraries[] = 'jStat';
                }
            }
        }

        return $externalFormulaScript;
    }
}
