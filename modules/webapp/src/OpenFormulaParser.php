<?php

namespace Appizy\WebApp;

class OpenFormulaParser
{
    const MS_EXCEL_NAMESPACE = 'msoxl';

    public function __construct()
    {
//        set_error_handler('self::errorHandle', E_ALL);
    }

//    static public function errorHandle()
//    {
//        print "Something just happened in formula Parser \n";
//    }

    /**
     * @param string $openFormula
     * @param integer $currentSheetIndex
     * @param string[] $sheetsNames
     * @param integer[] $cellCoordinates
     * @return Formula
     */
    public static function parse($openFormula, $currentSheetIndex, $sheetsNames, $cellCoordinates)
    {
        $formula = new Formula();

        $openFormula = self::cleanOdsFormula($openFormula);
        $lexicon = self::generateLexicon($openFormula);
        $formulaElements = self::lexer($openFormula, $lexicon);


        $cellDependencies = [];
        $offset = 0;
        foreach ($formulaElements as $key => $formula_element) {
            $temp = str_split($formula_element);

            if ($temp[0] == "[") {
                // If element starts with an "[" it's a range reference

                $external_ref = "file:";

                if (stripos($formula_element, $external_ref) === false) {

                    $formula_element = str_replace(array('[', ']'), '',
                        $formula_element);
                    $range_element = explode(":", $formula_element, 2);

                    if (count($range_element) == 2) {
                        // If RANGE of dimension 2 (more than 1 cell)
                        $head = self::referenceToCoordinates($range_element[0],
                            $currentSheetIndex, $sheetsNames);
                        $tail = self::referenceToCoordinates($range_element[1],
                            $currentSheetIndex, $sheetsNames);

                        $range_token = "[" . implode(',',
                                $head) . "],[" . implode(',', $tail) . "]";

                        for ($i = 0; $i <= $tail[1] - $head[1]; $i++) {
                            for ($j = 0; $j <= $tail[2] - $head[2]; $j++) {
                                $cS = $head[0];
                                $cR = $head[1] + $i;
                                $cC = $head[2] + $j;

                                $cellDependencies[] = array($cS, $cR, $cC);
                            }
                        }

                    } else {
                        // Simple RANGE, i.e one cell
                        $element = self::referenceToCoordinates($range_element[0],
                            $currentSheetIndex, $sheetsNames);
                        $dep_sheet = intval($element[0]);
                        $dep_row = intval($element[1]);
                        $dep_col = intval($element[2]);
                        $cellDependencies[] = array(
                            $dep_sheet,
                            $dep_row,
                            $dep_col
                        );
                        $range_token = "[" . implode(',', $element) . "]";

                    }

                    $replacement = array("RANGE", "(", $range_token, ")");
                    // Remove a portion of the array and replace it with something else
                    array_splice(
                        $formulaElements,
                        $key + $offset,
                        1,
                        $replacement
                    );

                    $offset += count($replacement) - 1;
                }
            }
        }

        $formula->setElements($formulaElements);
//        $formula->dependances = array_unique($cellDependencies);
        $formula->dependances = $cellDependencies;
        $formula->ext_formula_dependances = array_intersect(
            $formulaElements,
            WebAppConfiguration::externalFunctionDictionary()
        );
        $formula->cell_coord = $cellCoordinates;

        return $formula;
    }

    /**
     * @param string $formula
     * @param array $lexicon
     * @return array
     */
    private static function lexer($formula, $lexicon)
    {
        $index = 0;
        $lexedformula = [];

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
                if ($piece != "") : $lexedformula[$index] = self::lexer($piece,
                    $newlexicon); endif;
                $index = $index + 2;
            }
            // Avant de renvoyer on aplanit le tableau
            $flat_formula = array();
            array_walk_recursive($lexedformula,
                function ($a) use (&$flat_formula) {
                    $flat_formula[] = $a;
                });

            return $flat_formula;
        } else {

            return $formula;
        }
    }

    /**
     * @param string $reference cell coordinate as string
     * @param integer $referenceSheetIndex sheet index where the reference is
     * @param array $sheetsNames
     * @return array|bool
     */
    public static function referenceToCoordinates(
        $reference,
        $referenceSheetIndex,
        $sheetsNames
    )
    {
        // Sorts sheets' name by length to avoid inclusion issue
        uasort($sheetsNames, function ($a, $b) {
            return strlen($b) - strlen($a);
        });

        $sheet_name = array_values($sheetsNames);
        $sheet_index = array_keys($sheetsNames);

        // Replaces sheet name in $string by it's index
        $coord_in_string = str_replace($sheet_name, $sheet_index, $reference);

        // Removes additional '' and "" that can be around sheet's name
        $coord_in_string = str_replace(['"', "'"], "", $coord_in_string);


        if (preg_match('/^\./', $reference)) {
            $coord_in_string = $referenceSheetIndex . $coord_in_string;
        }

        // 2. Remplacement des lettres de colonnes
        preg_match_all('/[A-Z]/', $coord_in_string, $matches);
        $col_alpha = implode($matches[0]);
        $col_num = self::alphaToNumeric(implode($matches[0]));

        $coord_in_string = str_replace($col_alpha, $col_num . ",",
            $coord_in_string);

        $coord_in_string = str_replace(".", ",", $coord_in_string);

        $coord = explode(',', $coord_in_string);

        if (count($coord) == 3) {
            return array(
                intval($coord[0]),
                intval($coord[2]) - 1,
                intval($coord[1])
            );

        } else {
            trigger_error("Unable to get coord out of: $reference ");

            return false;
        }
    }

    /**
     * Convert a string (base 26) to integer (base 10)
     * Example: 'A' => 0, 'AA' => 26, 'AAA' => 702
     * @param $a
     * @return int
     */
    private static function alphaToNumeric($a)
    {
        $l = strlen($a);
        $n = 0;
        for ($i = 0; $i < $l; $i++) {
            $n = $n * 26 + ord($a[$i]) - 0x40;
        }

        return $n - 1;
    }

    /**
     * @param string $openFormula
     * @return string
     */
    private static function cleanOdsFormula($openFormula)
    {
        $formulaParts = explode(":=", $openFormula, 2);

        $namespace = $formulaParts[0];
        if ($namespace === self::MS_EXCEL_NAMESPACE) {
            // TODO: trigger an error here
            $formula = '';
        } else {
            trigger_error("hello", E_USER_WARNING);
            $formula = $formulaParts[1];
            // Remove '$' sign, not more necessary
            $formula = str_replace('$', '', $formula);
        }

        return $formula;
    }

    /**
     * @param string $openFormula
     * @return array
     */
    private static function generateLexicon($openFormula)
    {
        $operatorDictionary = WebAppConfiguration::operatorDictionary();
        $externalFunctionDictionary = WebAppConfiguration::externalFunctionDictionary();
        $referenceDictionary = self::generateReferenceDictionary($openFormula);
        $stringDictionary = self::generateStringDictionary($openFormula);

        $lexicon = array_merge($stringDictionary, $operatorDictionary, $externalFunctionDictionary,
            $referenceDictionary
        );

        // Sort by length to avoid inclusion effect
        uksort($lexicon, function ($a, $b) {
            return strlen($b) - strlen($a);
        });

        return $lexicon;
    }

    /**
     * @param string $openFormula
     * @return array
     */
    private static function generateStringDictionary($openFormula)
    {
        $strings_lex = [];

        preg_match_all("|\"(.*)\"|U", $openFormula, $out, PREG_PATTERN_ORDER);

        if (!empty($out[0])) {
            foreach ($out[0] as $string_informula) {
                $strings_lex[$string_informula] = $string_informula;
            }

        }

        return $strings_lex;
    }

    /**
     * @param string $openFormula
     * @return array
     */
    private static function generateReferenceDictionary($openFormula)
    {
        $strings_lex = [];

        preg_match_all('|\[.+\]|U', $openFormula, $out, PREG_PATTERN_ORDER);

        if (!empty($out[0])) {
            $strings_lex = [];
            foreach ($out[0] as $string_informula) {
                $strings_lex[$string_informula] = $string_informula;
            }
        }

        return $strings_lex;
    }
}
