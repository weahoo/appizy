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
        $htmlTable = '';

        $sheets_link = array(); // Array containing link to sheet anchors
        $used_styles = array(); // Contains styles used by the table elements.

        foreach ($this->sheets as $key => $sheet) {
            foreach ($sheet->row as $row_index => $row) {
                $rowstyle = ' class="' . $row->getName() . ' ' . $row->get_styles_name() . '"';

                $used_styles[] = $row->get_styles_name();

                foreach ($row->row_get_cells() as $cCI => $tempCell) {

                    if ($tempCell->cell_get_validation() != '') {
                        $this->render_validation($tempCell->cell_get_validation(),
                            array($key, $row_index, $cCI));
                        $tempCell->cell_set_type("in");
                    }

                    $td = "";

                    $tempstyle = $tempCell->get_styles_name();

                    $used_styles[] = $tempstyle;

                    $data_format = "";

                    $value_type = $tempCell->cell_value_type();

                    switch ($tempCell->getType()) {
                        case 'in':
                            $class = "in";
                            $list_values = $tempCell->getValueList();
                            if (empty($list_values)) {
                                $td .= '<input data-type="' . $value_type . '" ' . $data_format . ' id="' . $tempCell->getName() . '" name="' . $tempCell->getName() . '" type="text" value="' . $tempCell->cell_get_value() . '">';
                            } else {
                                $td .= '<select id="' . $tempCell->getName() . '" name="' . $tempCell->getName() . '">';
                                $value_attr = $tempCell->cell_get_value_attr();
                                foreach ($list_values as $value) {
                                    $selected = ($value == $value_attr) ? " selected" : "";
                                    $td .= '<option value="' . $value . '"' . $selected . '>' . $value . '</option>';
                                }
                                $td .= '</select>';
                            }
                            break;
                        case 'text':
                            $td .= $tempCell->cell_get_value_disp();
                            $class = "text";
                            break;
                        case 'out':
                            $td .= '<input data-type="' . $value_type . '" ' . $data_format . 'disabled name="' . $tempCell->getName() . '" value="' . $tempCell->cell_get_value_attr() . '">';
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

                        if ($temp_curcol->isCollapsed() == true) {
                            $tempCell->addStyle('hidden-cell');
                        }
                    }
                    $htmlTable .= '  <td class="' . $class . '">' . $td . '</td>' . "\n";
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

        //$variables['style'] = $style;
        $variables = [
            'content' => $htmlTable,
            'style' => $cssTable,
            'script' => $script,
            'libraries' => array_unique($libraries)
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
