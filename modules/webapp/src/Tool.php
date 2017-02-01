<?php

namespace Appizy\WebApp;

use Appizy\WebApp\Constant\ErrorMessage;

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
    /** @var  string[} */
    var $used_styles;

    private $debug;
    private $error;

    function __construct($debug = false)
    {
        $this->sheets = [];
        $this->styles = [];
        $this->formulas = [];
        $this->validations = [];
        $this->formats = [];
        $this->used_styles = [];
        $this->debug = $debug;
    }

    function addFormula($new_formula)
    {
        $this->formulas[] = $new_formula;
    }

    /**
     * @param $sheet_id Integer
     * @param $sheet_name String
     */
    function addSheet($sheet_id, $sheet_name)
    {
        $new_sheet = new Sheet($sheet_id, $sheet_name);
        $this->sheets[$sheet_id] = $new_sheet;
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
                    $tempcell = $this->getCell($dep[0], $dep[1], $dep[2]);
                    if ($tempcell) {
                        if ($tempcell->getType() != 'out') {
                            $tempcell->cell_set_type('in');
                        }
                    } else {
                        $cell_coord = $formula->cell_coord;
                        $referencedCellName = $this->getHumanizedCellName($dep[0], $dep[1], $dep[2]);
                        $formulaCellName = $this->getHumanizedCellName($cell_coord[0], $cell_coord[1], $cell_coord[2]);
                        trigger_error(ErrorMessage::NON_EXISTING_CELL . " The formula in cell $formulaCellName" .
                            " references an non-existing cell $referencedCellName");
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

                        $tempcell = $this->getCell($tmp_sI, $tmp_rI,
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
            $tempcell = $this->sheets[$tmp_sI]->row[$tmp_rI]->cells[$tmp_cI];
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

    /**
     * @param $sheet_index
     * @return Sheet|bool
     */
    function getSheet($sheet_index)
    {
        $sheet = false;
        if (array_key_exists($sheet_index, $this->sheets)) {
            $sheet = $this->sheets[$sheet_index];
        }

        return $sheet;
    }

    /**
     * @param $sheet_ind
     * @param $row_ind
     * @param $col_ind
     * @return Cell|bool
     */
    function getCell($sheet_ind, $row_ind, $col_ind)
    {
        $cell = false;

        $sheet = $this->getSheet($sheet_ind);

        if ($sheet) {
            $row = $sheet->getRow($row_ind);

            if ($row) {
                $cell = $row->getCell($col_ind);
            }
        }
        return $cell;
    }

    function clean()
    {
        $is_first_filled = false;
        $offset = 0;

        $reversedSheets = array_reverse($this->sheets, true);

        /** @var Sheet $temp_sheet */
        foreach ($reversedSheets as $temp_sheet) {

            $temp_sheet->removeEmptyRows();

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
            $reversedSheets = array_slice($reversedSheets, $offset);
        }
        // On inverse a nouveau et on affecte les sheets du tableau
        $sheets = array_reverse($reversedSheets, true);
        $this->sheets = $sheets;

    }

    function tool_render()
    {
        $htmlTable = '';

        $used_styles = array(); // Contains styles used by the table elements.

        foreach ($this->sheets as $key => $sheet) {
            foreach ($sheet->row as $row_index => $row) {
                $rowstyle = ' class="' . $row->getName() . ' ' . $row->get_styles_name() . '"';

                $used_styles[] = $row->get_styles_name();

                foreach ($row->getCells() as $cCI => $tempCell) {
                    if ($tempCell->cell_get_validation() != '') {
                        $this->render_validation($tempCell->cell_get_validation(),
                            array($key, $row_index, $cCI));
                        $tempCell->cell_set_type("in");
                    }

                    $used_styles[] = $tempCell->get_styles_name();

                    // Adds current col style (if exists)
                    try {
                        $currentColumn = $sheet->getCol($cCI);

                        $currentColumnStyle = '';
                        if ($currentColumn) {
                            $currentColumnStyle = $currentColumn->get_styles_name();

                            $used_styles[] = $currentColumnStyle;

                            if ($currentColumn->isCollapsed() == true) {
                                $tempCell->addStyle('hidden-cell');
                            }

                            $columnDefaultCellStyleId = $currentColumn->getDefaultCellStyle();
                            if ($columnDefaultCellStyleId !== '' && count($tempCell->getStyles()) === 0) {
                                $tempCell->addStyle($columnDefaultCellStyleId);
                                $used_styles[] = $columnDefaultCellStyleId;
                            }
                        }
                    } catch (\Exception $exception) {

                    }
                }
            }
        }

        $scriptBuilder = new WebAppScriptBuilder($this);
        $script = $scriptBuilder->buildScript();
        $libraries = $scriptBuilder->getExternalJsLibraries();

        // *** Code de la section CSS
        $used_styles = array_merge($used_styles, $this->used_styles);
        $used_styles = array_unique($used_styles);
        $cssTable = $this->getCss($used_styles);

        return [
            'content' => $htmlTable,
            'style' => $cssTable,
            'script' => $script,
            'libraries' => array_unique($libraries)
        ];
    }

    /**
     * @param string[] $stylesNames
     * @return string
     */
    function getCss($stylesNames)
    {
        $usedStyles = array_intersect_key($this->styles, array_flip($stylesNames));

        $css_code = '';
        /** @var Style $style */
        foreach ($usedStyles as $style) {
            $css_code .= $style->style_print();
        }

        return $css_code;
    }

    public function cleanStyles()
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

    private function getHumanizedCellName($sheetId, $rowId, $coId)
    {
        $sheetName = $this->getSheet($sheetId)->getName();
        $colName = Tool::num2alpha($coId);
        return "'$sheetName'." . $colName . ($rowId + 1);
    }

    private static function num2alpha($n)
    {
        $r = '';
        for ($i = 1; $n >= 0 && $i < 10; $i++) {
            $r = chr(0x41 + ($n % pow(26, $i) / pow(26, $i - 1))) . $r;
            $n -= pow(26, $i);
        }
        return $r;
    }
}
