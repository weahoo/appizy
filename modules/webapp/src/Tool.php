<?php

namespace Appizy\WebApp;

class Tool
{
    use ArrayTrait;

    /** @var Sheet[] */
    var $sheets;
    /** @var Style[] */
    var $styles;
    /** @var Formula[] */
    var $formulas;
    /** @var Validation[] */
    var $validations;
    /** @var DataStyle[] */
    var $formats;
    /** @var string[] */
    var $libraries;

    private $debug;
    private $error;

    function __construct($debug = false)
    {
        $this->sheets = [];
        $this->styles = [];
        $this->formulas = [];
        $this->libraries = [];
        $this->validations = [];
        $this->formats = [];
        $this->debug = $debug;
    }

    function tool_parse_wb($xml_path)
    {
        $extracted_ods = new OpenDocumentParser($xml_path, $this->debug);

        $this->sheets = $extracted_ods->sheets;
        $this->formulas = $extracted_ods->formulas;
        $this->styles = $extracted_ods->styles;
        $this->validations = $extracted_ods->validations;
        $this->formats = $extracted_ods->formats;

        $this->used_styles = $extracted_ods->used_styles;

        $this->setFormulaDependenciesAsInputCells();

        $this->cleanStyles();
    }

    function setFormulaDependenciesAsInputCells()
    {
        /** @var Formula $formula */
        foreach ($this->formulas as $formula) {
            if ($formula->isPrintable()) {
                $dependances = $formula->getDependencies();

                foreach ($dependances as $dep) {
                    $tempcell = $this->tool_get_cell($dep[0], $dep[1], $dep[2]);
                    if ($tempcell) {
                        if ($tempcell->getType() != 'out') {
                            $tempcell->cell_set_type('in');
                        }
                    } else {
                        // If cell doesn't exists
                        // Happens when formula's ranges are on empty cells
                        $new_cell = new cell($dep[0], $dep[1], $dep[2],
                            array("type" => 'in'));
                        //$this->tool_get_row($dep[0],$dep[1]);
                    }
                }
            }
        }
    }

    function sheets_name()
    {
        $names = [];
        foreach ($this->sheets as $sheet) {
            $names[] = $sheet->getName();
        }

        return $names;
    }

    function render_validation($id, array $address = null)
    {
        $sheets_name = $this->sheets_name();

        $validation = $this->validations[$id];

        if (array_key_exists('TABLE:CONDITION', $validation['attrs'])) {
            $temp_validation = str_replace('$', '',
                $validation['attrs']['TABLE:CONDITION']);
            $temp_validation_pieces = explode(":", $temp_validation, 2);
            $temp_validation = $temp_validation_pieces[1];
        } else {
            $temp_validation = "";
        }

        if (preg_match('/cell-content-is-in-list/', $temp_validation)) {
            // si validation de type "list de valeurs"

            preg_match_all("/cell-content-is-in-list\((.*)\)/",
                $temp_validation, $matches);
            $values = $matches[1][0];

            $temp_car = str_split($values);

            if ($temp_car[0] == '[') {
                // Range de valeurs
                $values = str_replace(array('[', ']'), '', $values);
                $values = explode(":", $values, 2);

                $head = OpenFormulaParser::referenceToCoordinates($values[0], 0, $sheets_name);

                $tail = OpenFormulaParser::referenceToCoordinates($values[1], $head[0],
                    $sheets_name);

                $values = array();
                for ($i = 0; $i <= $tail[1] - $head[1]; $i++) {
                    for ($j = 0; $j <= $tail[2] - $head[2]; $j++) {

                        $tmp_sI = $head[0];
                        $tmp_rI = $head[1] + $i;
                        $tmp_cI = $head[2] + $j;

                        $tempcell = $this->tool_get_cell($tmp_sI, $tmp_rI,
                            $tmp_cI);

                        if ($tempcell) {
                            $values[] = $tempcell->cell_get_value();
                        }
                    }
                }
            } else {
                $values = array();
                // Value list
                $validation_values = explode(";", $matches[1][0]);
                // Clean list
                foreach ($validation_values as $tmp_value) {
                    // Remove first and last chars, that are both "
                    $values[] = substr($tmp_value, 1, -1);
                }
            }

            // Set the list of potential values in the cell (adress)
            if (isset($address)) {
                $tmp_sI = $address[0];
                $tmp_rI = $address[1];
                $tmp_cI = $address[2];
            } else {
                $head = OpenFormulaParser::referenceToCoordinates($validation['attrs']['TABLE:BASE-CELL-ADDRESS'],
                    0, $sheets_name);
                $tmp_sI = $head[0];
                $tmp_rI = $head[1];
                $tmp_cI = $head[2];
            }
            $tempcell = $this->sheets[$tmp_sI]->row[$tmp_rI]->cell[$tmp_cI];
            $tempcell->setValueInList($values);
        }
    }

    function getSheets()
    {
        return $this->sheets;
    }

    function getStyle($styleName)
    {
        return self::array_attribute($this->styles, $styleName);
    }

    function getDataStyle($dataStyleName = '')
    {
        return self::array_attribute($this->formats, $dataStyleName);
    }

    function tool_get_sheet($sheet_index)
    {
        $sheets = $this->sheets;
        $sheet = false;
        if (array_key_exists($sheet_index, $sheets)) {
            $sheet = $sheets[$sheet_index];
        } else {
            $this->tool_error("Try to access unexistent sheet index:$sheet_index");
        }

        return $sheet;
    }

    function tool_get_cell($sheet_ind, $row_ind, $col_ind)
    {
        $cell = false;

        $sheet = $this->tool_get_sheet($sheet_ind);

        if ($sheet) {
            $cell = $sheet->sheet_get_cell($row_ind, $col_ind);
        }

        return $cell;
    }

    function tool_error($message)
    {
        trigger_error(__CLASS__ . '-' . __FUNCTION__ . ': ' . $message,
            E_USER_WARNING);
        //$this->error = $message;
    }

    function tool_debug($message)
    {
        trigger_error(__CLASS__ . ': ' . $message);
    }

    function tool_clean()
    {
        $is_first_filled = false;
        $offset = 0;

        // On inverse les sheets
        $sheets_reverse = array_reverse($this->sheets, true);

        // On nettoie ensuite chaque sheet
        foreach ($sheets_reverse as $temp_sheet) {

            $temp_sheet->sheet_clean();

            if (!$is_first_filled) :
                if ($temp_sheet->isEmptySheet()) {
                    $offset++;
                } else {
                    $is_first_filled = true;
                }
            endif;

        }
        // On supprime les $offset premi�res $sheet vides
        if ($offset > 0) {
            $sheets_reverse = array_slice($sheets_reverse, $offset);
        }
        // On inverse a nouveau et on affecte les sheets du tableau
        $sheets = array_reverse($sheets_reverse, true);
        $this->sheets = $sheets;

    }

    function tool_render()
    {
        $script = "";

        $formulas = "// Cells formulas" . "\n";
        $formulaslist = array();

        $htmlTable = '';

        $sheets_link = array(); // Array containing link to sheet anchors
        $used_styles = array(); // Contains styles used by the table elements.

        foreach ($this->sheets as $key => $sheet) {
            foreach ($sheet->row as $row_index => $row) {
                $rowstyle = ' class="' . $row->getName() . ' ' . $row->get_styles_name() . '"';

                $used_styles[] = $row->get_styles_name();

                foreach ($row->row_get_cells() as $cCI => $tempcell) {

                    if ($tempcell->cell_get_validation() != '') {
                        $this->render_validation($tempcell->cell_get_validation(),
                            array($key, $row_index, $cCI));
                        $tempcell->cell_set_type("in");
                    }

                    $td = "";

                    $tempstyle = $tempcell->get_styles_name();

                    $used_styles[] = $tempstyle;

                    $data_format = "";

                    $value_type = $tempcell->cell_value_type();

                    switch ($tempcell->getType()) {
                        case 'in':
                            $class = "in";
                            $list_values = $tempcell->getValueList();
                            if (empty($list_values)) {
                                $td .= '<input data-type="' . $value_type . '" ' . $data_format . ' id="' . $tempcell->getName() . '" name="' . $tempcell->getName() . '" type="text" value="' . $tempcell->cell_get_value() . '">';
                            } else {
                                $td .= '<select id="' . $tempcell->getName() . '" name="' . $tempcell->getName() . '">';
                                $value_attr = $tempcell->cell_get_value_attr();
                                foreach ($list_values as $value) {
                                    $selected = ($value == $value_attr) ? " selected" : "";
                                    $td .= '<option value="' . $value . '"' . $selected . '>' . $value . '</option>';
                                }
                                $td .= '</select>';
                            }
                            break;
                        case 'text':
                            $td .= $tempcell->cell_get_value_disp();
                            $class = "text";
                            break;
                        case 'out':
                            $td .= '<input data-type="' . $value_type . '" ' . $data_format . 'disabled name="' . $tempcell->getName() . '" value="' . $tempcell->cell_get_value_attr() . '">';
                            $class = "out";
                            break;
                    }

                    // Adds current cel style
                    if ($tempstyle != '') {
                        $class .= " " . $tempstyle;
                    }

                    // Adds current col style (if exists)
                    $temp_curcol_style = '';
                    $temp_curcol = $sheet->getCol($cCI);


                    if ($temp_curcol) {
                        $temp_curcol_style = $temp_curcol->get_styles_name();
                        if ($temp_curcol_style != '') {
                            $class .= " " . $temp_curcol_style;
                        }
                        $used_styles[] = $temp_curcol_style;

                        // Hidde cell if col is collapsed
                        if ($temp_curcol->get_collapsed() == true) {
                            $class .= " hidden-cell";
                        }
                    }
                    $htmlTable .= '  <td class="' . $class . '">' . $td . '</td>' . "\n";
                }
            }
        }

        /*
         * Cr�ation du script de calcul
         * ============================
         *
         * Javascipt plac� en oninput pour appeller les formules de calcul
         * Javascrip plac� apr�s le tableau dans le formulaire
         *
         * Etapes :
         * = Tri des formules, i.e cr�ation de l'arbre de calcul
         * = Cr�ation du oninput
         *
        */


        /*
         *  = Tri des formules
         *  ==================
         *
         *  Pour le moment nous avons l'ensemble des formules dans l'Array
         *  $formulalist. Nous allons dans un premier temps organiser les
         *  formules en "step" de calcul.
         *
         *  Une step correspond � un ensemble de formules calcul�e en m�me temps.
         *  Le fonctionnement en step permet de mettre � jour toutes les formules
         *  � chaque modification d'un param�tre (commen dans un tableur) en une
         *  seule passe, c.a.d en calculant chaque formule une seule fois. Il
         *  faut d�terminer dans les interd�pendances des formules et calculer
         *  dans le bon ordre. C'est l'objet de cette premi�re boucle "while"
         *
        */
        $steps = [];
        $ext_formulas = [];

        /** @var Formula $formula */
        foreach ($this->formulas as $formula) {
            $dependances = array();
            foreach ($formula->getDependencies() as $dependance) {
                $dependances[] = 's' . $dependance[0] . 'r' . $dependance[1] . 'c' . $dependance[2];
            }

            $formulas .= $formula->getScript() . "\n";
            $formulaslist[$formula->getName()] = [
                'call' => $formula->getCall(),
                'dep'  => $dependances,
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
        $oninput = "";
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

        // Get external formulas
        if (!empty($formulascall)) {
            $script .= "(function() {" . "\n";

            $formulas_ext = "var root = this;" . "\n";
            $formulas_ext .= "var Formula = root.Formula = {};" . "\n";
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
                    __DIR__ . "/../assets/js/src/formula.js");
            }


            $script .= $run_calc;
            $script .= $formulascall;
            $script .= $formulas;
            $script .= $formulas_ext;
            $script .= "}).call();" . "\n";
        }

        // D�but du tableau
        $htmlHead = '<!-- The code of your app starts just below. Thank you for using Appizy. -->' . "\n";

        $htmlTable = '<div id="appizy">' . "\n" . '<form' . $oninput . '>' . "\n" . $htmlTable;

        // Fin du tableau
        $htmlTable .= '</form>' . "\n" . '</div><!-- /#apppizy -->' . "\n";

        // *** Code de la section CSS
        $used_styles = array_merge($used_styles, $this->used_styles);

        $used_styles = array_unique($used_styles);


        $cssTable = $this->getCss($used_styles);

        //$variables['style'] = $style;
        $variables = [
            'content'   => $htmlTable,
            'style'     => $cssTable,
            'script'    => $script,
            'libraries' => array_unique($this->libraries)
        ];

        return $variables;
    }

    /**
     * @param array $used_styles
     * @return string
     */
    function getCss($used_styles)
    {
        $css_code = '';

        $used_styles = array_flip($used_styles);
        // Gets intersection of used and available styles
        $used_styles = array_intersect_key($this->styles, $used_styles);

        foreach ($used_styles as $key => $value) {
            $css_code .= $value->style_print();
        }

        return $css_code;
    }

    /**
     * @param string $function_name
     * @param string $library_path
     * @param array  $already_loaded
     * @return string
     */
    function getExtFunction($function_name, $library_path, $already_loaded = [])
    {
        $externalFormulaScript = '';
        $namu = $function_name;
        $function_name = preg_quote($function_name);

        $formulaRegex = '/' . $function_name . ' = function(.*?)\};/is';

        if (!in_array($function_name, $already_loaded)) {

            if (preg_match_all($formulaRegex, file_get_contents($library_path), $match)) {
                $function = $match[1][0];
                $externalFormulaScript = $namu . " = function" . $function . "};" . "\n\n";


                $already_loaded[] = $namu;

                if (preg_match_all('/Formula.(.*?)\(/is', $function, $match)) {
                    // The current function depends on other function (having the form 'Formula.XYZ')
                    $match = array_unique($match[1]);
                    $match = array_diff($match, $already_loaded);
                    foreach ($match as $dep_name) {

                        $dep_name = "Formula." . $dep_name;

                        if (!in_array($dep_name, $already_loaded)) {
                            $externalFormulaScript .= $this->getExtFunction($dep_name,
                                $library_path, $already_loaded);
                        }
                    }
                }

                if (preg_match_all('/jStat.(.*?)\(/is', $function, $match)) {
                    $this->libraries[] = 'jStat';
                }
            }
        }

        return $externalFormulaScript;
    }

    private function cleanStyles()
    {
        foreach ($this->styles as $id => $style) {
            $parent_style_name = $style->parent_style_name;

            if ($parent_style_name != '') {
                $parent_style = $this->styles[$parent_style_name];
                $style->style_merge($parent_style);
                $this->styles[$id] = $style;
            }
        }
    }
}
