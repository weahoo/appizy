<?php

namespace Appizy\WebApp;

class Formula
{

    var $cell_coord; // coordonn�es de la cellule d'origine
    var $formula_elements;
    var $dependances; // Tableau des coordonn�es de cellules dont d�pent la formule
    var $ext_formula_dependances; // Liste des fonctions externes dont d�pend la formule

    function __construct($coord = [], $ods_formula = '', $current_sheet = 0, $sheets_name = [])
    {
        $this->cell_coord = $coord;

        $broken_ref = "#REF!";
        if (stripos($ods_formula, $broken_ref) === false) {
            $this->formula_crude2elements($ods_formula, $current_sheet, $sheets_name);
            $this->error[] = "Broken cell reference in the formula";
        }
    }

    function formula_crude2elements($ods_formula = '', $current_sheet = 0, $sheets_name = [])
    {
        // Step 1 - clean crude ODS formula
        $temp_formula = explode("=", $ods_formula, 2); // remove "=" sign at the beginning
        $temp_formula = $temp_formula[1];

        $temp_formula = str_replace('$', '', $temp_formula); // remove all "$"

        // Step 2 - lexion preparation
        // ****** 2.1 - Mathematical chars
        $formula_lexicon = array('+'  => '+',
                                 '-'  => '-',
                                 '/'  => '/',
                                 '*'  => '*',
                                 '('  => '(',
                                 ')'  => ')',
                                 ';'  => ',',
                                 ','  => ',',
                                 '<>' => '!=',
                                 '<=' => '<=',
                                 '>=' => '>=',
                                 '<'  => '<',
                                 '>'  => '>',
                                 '='  => '==',
                                 '&'  => '+',
            /**
             * CORRECTORS:
             * - Remove additional spaces in formula
             * - Removes "++" accepted by Excel/OO
             *
             */
                                 ' '  => '',
                                 '++' => '+',
        );

        // ****** 2.2 - External formulas available
        $ext_formulas_available = array(
            /* Mathematical */
            'SUM', 'SUMIF', 'SUMPRODUCT',
            'COS', 'ACOS', 'SIN', 'ASIN', 'TAN', 'ATAN', 'PI', 'POWER', 'SQRT',
            'MAX', 'MIN', 'AVERAGE', 'RADIANS', 'DEGREES',
            'ROUND', 'CEILING', 'FLOOR', 'ROUNDUP', 'ROUNDDOWN', 'INT', 'TRUNC',
            'MROUND',
            'ABS',
            'GCD', 'LCM', 'MOD',
            'RAND', 'RANDBETWEEN',
            'QUOTIENT', 'PRODUCT',
            /* Financial */
            'PMT',
            /* Statistical */
            'RANK',
            /* Logical */
            'AND', 'FALSE', 'IF', 'IFERROR', 'IFNA', 'NOT', 'OR', 'TRUE', 'XOR',
            'ISBLANK',
            /* Text */
            'DOLLAR', 'CONCATENATE', 'HYPERLINK',
            /* SEARCH */
            'VLOOKUP', 'LOOKUP', 'COUNTIF'
            /* TIME */
        );

        // Create the array that associate Ods formula name and Formula.js name
        $ext_formulas = array();
        foreach ($ext_formulas_available as $ext_formula) {
            $ext_formulas[$ext_formula] = 'Formula.' . $ext_formula;
        }

        // ****** 2.3 - Merge previous lexicons
        $lexicon = array_merge($formula_lexicon, $ext_formulas);

        // ****** 2.4 - Search for strings between " " and add it to lexicon
        preg_match_all("|\"(.*)\"|U", $temp_formula, $out, PREG_PATTERN_ORDER);

        if (!empty($out[0])) {
            $strings_lex = array();
            foreach ($out[0] as $string_informula) {
                $strings_lex[$string_informula] = $string_informula;
            }
            $lexicon = array_merge($strings_lex, $lexicon);
        }

        // ****** 2.5 - Sort Lexicon by string lenght to avoid inclusion issues
        uksort($lexicon, function ($a, $b) {  return strlen($b) - strlen($a); });

        // ****** 2.6 - Lexicon decomposistion
        $temp_formula = lexer($temp_formula, $lexicon);

        // ****** 2.7 - Checking ext formulas dependances and updating Formula var
        $this->ext_formula_dependances = array_intersect($temp_formula, $ext_formulas);

        // ****** 2.8 - Range detection and modification
        $this->formula_convert_cellranges($temp_formula, $current_sheet, $sheets_name);
    }

    function formula_convert_cellranges($array_formula = array(), $current_sheet = 0, $sheets_name = array())
    {

        $celldependancies = array();
        $offset = 0;

        foreach ($array_formula as $key => $formula_element) {
            // Pour chaque �l�ment de la formule
            $temp = str_split($formula_element); // A remplacer par expression r�guli�re

            if ($temp[0] == "[") {
                // If element starts with an "[" it's a range reference

                $external_ref = "file:";

                if (stripos($formula_element, $external_ref) === false) {

                    $formula_element = str_replace(array('[', ']'), '', $formula_element);
                    $range_element = explode(":", $formula_element, 2);

                    if (count($range_element) == 2) {
                        // If RANGE of dimension 2 (more than 1 cell)
                        $head = string2coord($range_element[0], $current_sheet, $sheets_name);
                        $tail = string2coord($range_element[1], $current_sheet, $sheets_name);

                        $range_token = "[" . implode(',', $head) . "],[" . implode(',', $tail) . "]";

                        for ($i = 0; $i <= $tail[1] - $head[1]; $i++) {
                            for ($j = 0; $j <= $tail[2] - $head[2]; $j++) {
                                $cS = $head[0];
                                $cR = $head[1] + $i;
                                $cC = $head[2] + $j;

                                $celldependancies[] = array($cS, $cR, $cC);

                            }
                        }

                    } else {
                        // Simple RANGE, i.e one cell
                        $element = string2coord($range_element[0], $current_sheet, $sheets_name);
                        $dep_sheet = intval($element[0]);
                        $dep_row = intval($element[1]);
                        $dep_col = intval($element[2]);
                        $celldependancies[] = array($dep_sheet, $dep_row, $dep_col);
                        $range_token = "[" . implode(',', $element) . "]";

                    }

                    $replacement = array("RANGE", "(", $range_token, ")");
                    // Remove a portion of the array and replace it with something else
                    array_splice(
                        $array_formula, // Formula
                        $key + $offset,   // Place of the token in the formula
                        1,              // Length of the token = 1
                        $replacement    // Inserted tokens
                    );

                    $offset += count($replacement) - 1;

                } else {
                    // Formula contains external references
                    $this->error[] = "External references not supported";
                    // Delete the formula content
                }

            } // End if element is a RANGE

        } // End foreach formula element

        // $celldependancies = array_unique($celldependancies); // Chaque d�pendance est unique

        $this->dependances = $celldependancies;
        $this->formula_elements = $array_formula;
    }

    function get_call()
    {
        return $this->get_name() . '();';
    }

    function get_name()
    {
        $coord = $this->cell_coord;

        return 's' . $coord[0] . 'r' . $coord[1] . 'c' . $coord[2];
    }

    function get_dependances()
    {
        // trigger_error("Get dep of:".$this->get_name(), E_USER_WARNING);
        return $this->dependances;
    }

    function get_ext_formula()
    {
        return $this->ext_formula_dependances;
    }

    function get_script()
    {
        $formula_name = $this->get_name();
        $script = "";
        foreach ($this->formula_elements as $formula_element) {
            $script .= $formula_element;
        }

        return "function " . $formula_name . "(){ APY.set('" . $formula_name . "'," . $script . ") }";

    }

    function formula_isprintable()
    {
        return $this->error;
    }
}

class Validation
{
    private $cell_coord;
    private $condition;
}

/*
 * Formula Handle
 *
 * Ensemble de fonctions pour traiter une formule de calcul OpenDocument
*/

// D�compose une $formula brute (String) en �l�ments unitaires d'un $lexicon (Array) pass� en param�tre
function lexer($formula, $lexicon)
{
    $index = 0;
    $lexedformula = array();

    // Longeur du lexique
    $size = count($lexicon);

    if ($size > 0) {
        // Si le lexique contient un �l�ment on d�compose suivante cet �l�ment

        // La formule pass�e en param�tre est d�compos�e suivant le terme du lexique ODS($lexico)
        list($odslexicon, $javalexicon) = each($lexicon);

        $formulapiece = explode($odslexicon, $formula);

        // l'array retourn� dans ce cas est compos� des morceaux de formules que l'on continue de d�compos�e
        // avec les termes du lexique entre
        $isFirst = true;
        foreach ($formulapiece as $piece) {
            if ($isFirst) {
                $isFirst = false;
            } else {
                $lexedformula[$index - 1] = $javalexicon;
            }
            $newlexicon = array_slice($lexicon, 1);

            // avant de lancer la r�curcivit� on v�rifie que le morceau de formule n'est pas vide
            if ($piece != "") : $lexedformula[$index] = lexer($piece, $newlexicon); endif;
            $index = $index + 2;
        }
        // Avant de renvoyer on aplanit le tableau
        $flat_formula = array();
        array_walk_recursive($lexedformula, function ($a) use (&$flat_formula) {
                $flat_formula[] = $a;
            });

        return $flat_formula;
    } else {
        // sinon on renvoie la formule pass� en param�tre
        return $formula;
    }
}


/**
 * Transforme des coordon�es de cellule ODS en coordonn�es cart�siennes
 */
function string2coord($string, $currentsheet, $sheetname)
{

    // Sorts sheets' name by length to avoid inclusion issue
    uasort($sheetname, function ($a, $b) {  return strlen($b) - strlen($a); });

    $sheet_name = array_values($sheetname);
    $sheet_index = array_keys($sheetname);

    // Replaces sheet name in $string by it's index
    $coord_in_string = str_replace($sheet_name, $sheet_index, $string);

    // Removes additional '' and "" that can be around sheet's name
    $coord_in_string = str_replace(array('"', "'"), "", $coord_in_string);


    if (preg_match("/^\./", $string)) $coord_in_string = $currentsheet . $coord_in_string;

    // 2. Remplacement des lettres de colonnes
    preg_match_all('/[A-Z]/', $coord_in_string, $matches);
    $col_alpha = implode($matches[0]);
    $col_num = alpha2num(implode($matches[0]));

    $coord_in_string = str_replace($col_alpha, $col_num . ",", $coord_in_string);

    $coord_in_string = str_replace(".", ",", $coord_in_string);

    $coord = explode(',', $coord_in_string);

    if (count($coord) == 3) {
        // If $coord has the correct number to value

        return array(intval($coord[0]), intval($coord[2]) - 1, intval($coord[1]));

    } else {
        trigger_error("Unable to get coord out of: $string ");

        return false;
    }
}

// Pour sortir les �l�ments d'un tableau par taille
function sortBylength($a, $b)
{
    return strlen($b) - strlen($a);
}

// Transforme une cha�ne alpha en num, base 26
function alpha2num($a)
{
    $l = strlen($a);
    $n = 0;
    for ($i = 0; $i < $l; $i++)
        $n = $n * 26 + ord($a[$i]) - 0x40;

    return $n - 1;
}
