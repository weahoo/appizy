<?php

namespace Appizy\WebApp;

$globaldata = "";

class OpenDocumentParser
{
    use ArrayTrait;

    var $fonts;
    var $styles;
    var $sheets;
    var $lastElement;
    var $currentSheet;
    var $currentRow;
    var $currentCell;
    var $currentFrame;
    var $lastRowAtt;
    var $repeatCell;
    var $repeatRow;
    var $validation;
    var $currentValidation;
    var $currentStyle;
    var $currentDataStyle;

    var $contenttag_stack;
    //public $globaldata;

    var $used_styles;
    var $debug;
    /** @var Tool */
    var $spreadsheet;
    /** @var int */
    private $totalCell;
    /** @var int */
    private $maxCellsNumber;
    /** @var int */
    private $currentColumn;
    /** @var int */
    private $currentAttrs;

    /**
     * @param int $maxCellsNumber
     */
    function __construct($maxCellsNumber = -1)
    {
        $this->spreadsheet = new Tool();
        $this->styles = array();
        $this->fonts = array();
        $this->sheets = array();
        $this->used_styles = array();
        $this->currentSheet = 0;
        $this->currentColumn = 0;
        $this->currentRow = 0;
        $this->currentAttrs = 0;
        $this->currentCell = 0;
        $this->currentFrame = 0;
        $this->currentValidation = 0;
        $this->debug = true;
        $this->contenttag_stack = array(); // Stack of tags that can have "Content"
        $this->totalCell = 0;
        $this->maxCellsNumber = $maxCellsNumber;
    }

    /**
     * Adds a new row to the selected wb_sheet
     */
    function addRow($sheet_ind, $row_ind, $options)
    {
        $new_row = new Row($sheet_ind, $row_ind, $options);

        /** @var Sheet $sheet */
        $sheet = $this->spreadsheet->sheets[$sheet_ind];
        $sheet->addRow($new_row);
    }

    function addCell($sheet_ind, $row_ind, $col_ind, $options)
    {
        $new_cell = new Cell($sheet_ind, $row_ind, $col_ind, $options);
        // Get selected row in the selected sheet
        /** @var Sheet $sheet */
        $sheet = $this->spreadsheet->sheets[$sheet_ind];
        /** @var Row $row */
        $row = $sheet->getRow($row_ind);

        $row->addCell($new_cell);
    }

    function addCol($sheet_ind, $new_col)
    {
        $sheet = $this->spreadsheet->sheets[$sheet_ind];
        $sheet->addCol($new_col);
    }

    function orderData()
    {
        $sheetsNames = array();
        foreach ($this->sheets as $currentSheetIndex => $sheet) {
            $name = $sheet['TABLE:NAME'];
            /**
             * Sheet name with just a dot
             */
            if ($name == '.') {
                $name = "'" . $name . "'";
            }
            $sheetsNames[] = $name;
        }

        foreach ($this->sheets as $currentSheetIndex => $sheet) {
            $this->spreadsheet->addSheet($currentSheetIndex, htmlentities($sheet['TABLE:NAME'], ENT_QUOTES, "UTF-8"));

            if (array_key_exists('column', $sheet)) {
                foreach ($sheet['column'] as $curCOLI => $col) {
                    $tempcol = new Column($curCOLI);

                    if (array_key_exists('attrs', $col)) {
                        if (array_key_exists('TABLE:STYLE-NAME', $col['attrs'])) {
                            $tempcol->addStyle($col['attrs']['TABLE:STYLE-NAME']);
                        }

                        if (array_key_exists('TABLE:VISIBILITY', $col['attrs'])) {
                            if ($col['attrs']['TABLE:VISIBILITY'] == 'collapse') {
                                $tempcol->collapse();
                            }
                        }

                        if (array_key_exists('TABLE:DEFAULT-CELL-STYLE-NAME',
                            $col['attrs'])) {
                            $tempcol->col_set_default_cell_style($col['attrs']['TABLE:DEFAULT-CELL-STYLE-NAME']);
                        }
                    }
                    $this->addCol($currentSheetIndex, $tempcol);
                }
            }

            if (array_key_exists('rows', $sheet)) {
                foreach ($sheet['rows'] as $cR => $row) {

                    $row_options = array();
                    if (array_key_exists('attrs', $row)) {
                        if (array_key_exists('TABLE:STYLE-NAME', $row['attrs'])) {
                            $row_options['style'] = htmlentities($row['attrs']['TABLE:STYLE-NAME'],
                                ENT_QUOTES, "UTF-8");
                        }

                        if (array_key_exists('TABLE:VISIBILITY', $row['attrs'])) {
                            $row_options['collapse'] = ($row['attrs']['TABLE:VISIBILITY'] == 'collapse');
                        }

                        if (array_key_exists('TABLE:STYLE-NAME', $row['attrs'])) {
                            $row_options['style'] = $row['attrs']['TABLE:STYLE-NAME'];
                        }
                    }
                    $this->addRow($currentSheetIndex, $cR, $row_options);

                    if (array_key_exists('cells', $row)) {
                        // If there are cells in the row
                        foreach ($row['cells'] as $cC => $cell) {

                            $cell_options = array();

                            if (array_key_exists('attrs', $cell)) {
                                if (array_key_exists('TABLE:NUMBER-ROWS-SPANNED', $cell['attrs'])) {
                                    $cell_options['rowspan'] = $cell['attrs']['TABLE:NUMBER-ROWS-SPANNED'];
                                }

                                if (array_key_exists('TABLE:NUMBER-COLUMNS-SPANNED', $cell['attrs'])) {
                                    $cell_options['colspan'] = $cell['attrs']['TABLE:NUMBER-COLUMNS-SPANNED'];
                                }

                                if (array_key_exists('TABLE:STYLE-NAME', $cell['attrs'])) {
                                    $cell_options['style'] = strtolower($cell['attrs']['TABLE:STYLE-NAME']);
                                } else {
                                    if ($default_style = $this->getColDefaultCellStyle($currentSheetIndex, $cC)) {
                                        $cell_options['style'] = strtolower($default_style);
                                    }

                                }
                                if (array_key_exists('OFFICE:VALUE', $cell['attrs'])) {
                                    $cell_options['value_attr'] = htmlentities($cell['attrs']['OFFICE:VALUE'],
                                        ENT_QUOTES, "UTF-8");
                                }

                                if (array_key_exists('OFFICE:BOOLEAN-VALUE', $cell['attrs'])) {
                                    $cell_options['value_attr'] = htmlentities($cell['attrs']['OFFICE:BOOLEAN-VALUE'],
                                        ENT_QUOTES, "UTF-8");
                                }

                                if (array_key_exists("OFFICE:VALUE-TYPE", $cell['attrs'])) {
                                    $cell_options['value_type'] = $cell['attrs']['OFFICE:VALUE-TYPE'];
                                }

                                if (array_key_exists("TABLE:CONTENT-VALIDATION-NAME", $cell['attrs'])) {
                                    $cell_options['validation'] = $cell['attrs']["TABLE:CONTENT-VALIDATION-NAME"];
                                }

                                if (array_key_exists("TABLE:FORMULA", $cell['attrs'])) {
                                    $openFormula = $cell['attrs']['TABLE:FORMULA'];
                                    $cellCoordinates = [$currentSheetIndex, $cR, $cC];
                                    $formula = OpenFormulaParser::parse($openFormula, $currentSheetIndex, $sheetsNames,
                                        $cellCoordinates);

                                    if ($formula->isPrintable()) {
                                        $this->spreadsheet->addFormula($formula);
                                    }

                                    $cell_options['type'] = "out";
                                }
                            }

                            if (array_key_exists('value', $cell)) {
                                $cell_options['value_disp'] = $cell['value'];
                            }

                            if (array_key_exists('annotation', $cell)) {
                                $cell_options['annotation'] = $cell['annotation'];
                            }
                            $this->addCell($currentSheetIndex, $cR, $cC, $cell_options);
                        }
                    }
                }
            }
        }
    }

    function parse($filenames)
    {
        foreach ($filenames as $filename) {

            $xmlContent = file_get_contents($filename);
            $xml_parser = xml_parser_create();
            xml_set_object($xml_parser, $this);
            xml_set_element_handler($xml_parser, "startElement", "endElement");
            xml_set_character_data_handler($xml_parser, "characterData");
            xml_parser_set_option($xml_parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
            xml_parse($xml_parser, $xmlContent, false);
            xml_parser_free($xml_parser);
        }

        $this->orderData();

        return $this->spreadsheet;
    }

    function startElement($parser, $tagName, $attrs)
    {
        $cTagName = strtolower($tagName);

        if ($cTagName == 'style:font-face') {
            $this->fonts[$attrs['STYLE:NAME']] = $attrs;
        } elseif ($cTagName == 'style:style') {
            $this->lastElement = $cTagName;

            $id = self::array_attribute($attrs, 'STYLE:NAME');

            $new_style = new Style(strtolower($id));
            $new_style->data_style_name = self::array_attribute($attrs,
                'STYLE:DATA-STYLE-NAME');
            $new_style->parent_style_name = strtolower(self::array_attribute($attrs,
                'STYLE:PARENT-STYLE-NAME'));

            $this->currentStyle = $new_style;

        } elseif ($cTagName == 'style:table-column-properties'
            || $cTagName == 'style:table-row-properties'
            || $cTagName == 'style:table-properties'
            || $cTagName == 'style:text-properties'
            || $cTagName == 'style:table-cell-properties'
            || $cTagName == 'style:paragraph-properties'
            && isset($this->currentStyle)
        ) {

            if (isset($this->currentStyle)) {

                $current_style = $this->currentStyle;

                $current_style->addOdsStyles($attrs);

                $this->currentStyle = $current_style;
            }

        } elseif ($cTagName == 'table:covered-table-cell') {
            $this->lastElement = $cTagName;

            $cov_cell_repeated = 1;
            if (isset($attrs['TABLE:NUMBER-COLUMNS-REPEATED'])) {
                $cov_cell_repeated = intval($attrs['TABLE:NUMBER-COLUMNS-REPEATED']);
            }

            $this->currentCell += $cov_cell_repeated;
            /**
             * XML tags related to cell-content
             */
        } elseif ($cTagName == 'table:table-cell') {

            // on vide $globaldata pour la prochaine cellule
            global $globaldata;
            $globaldata = "";

            // Si le tag est une cellule
            $this->lastElement = $cTagName;

            $this->contenttag_stack[] = $cTagName;

            $cell_repeated = 1; // Nombre de répétition d'une cellule, par défaut = 1
            if (isset($attrs['TABLE:NUMBER-COLUMNS-REPEATED'])) {
                $cell_repeated = intval($attrs['TABLE:NUMBER-COLUMNS-REPEATED']);
            }

            $this->repeatCell = $cell_repeated;

            if ($cell_repeated < 100) {
                for ($i = 0; $i < $this->repeatRow; $i++) {
                    // Pour chaque ligne repétée
                    $current_row_ind = $this->currentRow + $i;

                    for ($j = 0; $j < $cell_repeated; $j++) {
                        $current_cell_ind = $this->currentCell + $j;
                        $this->sheets[$this->currentSheet]['rows'][$current_row_ind]['cells'][$current_cell_ind]['attrs'] = $attrs;
                    }
                }
                $this->currentCell += $cell_repeated - 1;
            }
        } elseif ($cTagName == 'office:annotation') {
            $this->lastElement = $cTagName;
            $this->contenttag_stack[] = $cTagName;

        } elseif ($cTagName == 'draw:frame') {
            $this->lastElement = $cTagName;
            $this->sheets[$this->currentSheet]['rows'][$this->currentRow]['cells'][$this->currentCell]['frame'][$this->currentFrame]['attrs'] = $attrs;

        } elseif ($cTagName == 'draw:image') {
            $this->lastElement = $cTagName;
            $this->sheets[$this->currentSheet]['rows'][$this->currentRow]['cells'][$this->currentCell]['frame'][$this->currentFrame]['image'] = $attrs;

        } elseif ($cTagName == 'draw:object') {
            $this->lastElement = $cTagName;
            $this->sheets[$this->currentSheet]['rows'][$this->currentRow]['cells'][$this->currentCell]['frame'][$this->currentFrame]['objet'] = $attrs;

        } elseif ($cTagName == 'table:table-row') {
            $this->lastElement = $cTagName;

            $row_repeated = 1; // Nombre de répétitions de la ligne, par défaut = 1
            if (isset($attrs['TABLE:NUMBER-ROWS-REPEATED'])) {
                $row_repeated = intval($attrs['TABLE:NUMBER-ROWS-REPEATED']);
            }

            if ($row_repeated < 100) {
                // Si la ligne est répétée moins de 100 fois
                //logapp(LOGAPPPATH,'Row repeated '.$this->currentRow.' '.$row_repeated.' fois');
                for ($i = 0; $i < $row_repeated; $i++) {

                    $current_row_ind = $this->currentRow + $i;
                    //logapp(LOGAPPPATH,'Ajout row '.$current_row_ind.' Row-repeated de '.$this->currentRow);
                    $this->sheets[$this->currentSheet]['rows'][$current_row_ind]['attrs'] = $attrs;
                }
                // $this->currentRow = $row; // Pas besoin de mettre à jour ici, c'est dans le tag cellule que ça se passe
                $this->repeatRow = $row_repeated;
            }
        } elseif ($cTagName == 'table:table') {
            $this->lastElement = $cTagName;
            $this->sheets[$this->currentSheet] = $attrs;

        } elseif ($cTagName == 'table:content-validation') {
            $this->lastElement = $cTagName;
            $id = $attrs['TABLE:NAME'];
            $this->spreadsheet->validations[$id]['attrs'] = $attrs;

        } elseif ($cTagName == 'table:table-column') {
            $this->lastElement = $cTagName;
            $col_repeated = 1;

            if (isset($attrs['TABLE:NUMBER-COLUMNS-REPEATED'])) {
                $col_repeated = intval($attrs['TABLE:NUMBER-COLUMNS-REPEATED']);
            }

            if ($col_repeated < 100) {
                for ($i = 0; $i < $col_repeated; $i++) {
                    $current_coll_ind = $this->currentColumn + $i;
                    $this->sheets[$this->currentSheet]['column'][$current_coll_ind]['attrs'] = $attrs;
                }
            } else {
                $current_coll_ind = $this->currentColumn;
                $this->sheets[$this->currentSheet]['column'][$current_coll_ind]['attrs'] = $attrs;
                $col_repeated = 1;
            }
            $this->currentColumn += $col_repeated - 1;

        } elseif ($cTagName == 'text:tab') {
            global $globaldata;

            $globaldata .= "&nbsp;";

        } elseif ($cTagName == 'text:p') {
            global $globaldata;

            $class = "";
            if ($style_name = strtolower(self::array_attribute($attrs,
                'TEXT:STYLE-NAME'))
            ) {
                $class = ' class="' . $style_name . '" ';
                $this->spreadsheet->used_styles[] = $style_name;
            }

            $globaldata .= "<p$class>";

        } elseif ($cTagName == 'text:span') {
            global $globaldata;

            $class = "";
            if ($style_name = strtolower(self::array_attribute($attrs,
                'TEXT:STYLE-NAME'))
            ) {
                $class = ' class="' . strtolower($style_name) . '" ';
                $this->spreadsheet->used_styles[] = $style_name;
            }

            $globaldata .= "<span$class>";
        } elseif ($cTagName == 'number:number-style' || $cTagName == 'number:currency-style' ||
            $cTagName == 'number:percentage-style'
        ) {

            $this->lastElement = $cTagName;

            // Contenttag to prefix
            $this->new_contenttag('data-style-prefix');

            $id = self::array_attribute($attrs, 'STYLE:NAME');

            $this->currentDataStyle = new DataStyle($id);

        } elseif (isset($this->currentDataStyle)) {
            /**
             * Search for tagname only if DataStyle set
             */
            if ($cTagName == 'number:number') {

                // Format of the number itself
                $data_style = $this->currentDataStyle;

                $data_style->min_int_digit = intval(self::array_attribute($attrs,
                    'NUMBER:MIN-INTEGER-DIGITS'));
                $data_style->decimal_places = intval(self::array_attribute($attrs,
                    'NUMBER:DECIMAL-PLACES'));

                $this->currentDataStyle = $data_style;

                // Change contenttag to suffix
                $this->new_contenttag('data-style-suffix');

            } elseif ($cTagName == 'style:map') {
                // Mapping of the DataStyle
                $condition = self::array_attribute($attrs, 'STYLE:CONDITION');
                $apply_style_name = self::array_attribute($attrs,
                    'STYLE:APPLY-STYLE-NAME');

                $data_style = $this->currentDataStyle;

                $data_style->maps[$condition] = $apply_style_name;

            } elseif ($cTagName == 'number:currency-symbol') {
                // Helps to escape currency symbo different from "$"
                $this->lastElement = $cTagName;
            }
        }
    }

    function endElement($parser, $tagName)
    {
        global $globaldata;

        $cTagName = strtolower($tagName);

        if ($cTagName == 'table:table') {
            $this->currentSheet++;
            $this->currentRow = 0;
            $this->currentColumn = 0;
        } elseif ($cTagName == 'table:content-validation') {
            $this->currentValidation++;
        } elseif ($cTagName == 'table:table-column') {
            $this->currentColumn++;
        } elseif ($cTagName == 'table:table-row') {
            $this->currentRow += $this->repeatRow;
            $this->currentCell = 0;
        } elseif ($cTagName == 'table:table-cell') {
            $this->totalCell++;
            if ($this->maxCellsNumber > 0 && $this->totalCell > $this->maxCellsNumber) {
                trigger_error("maximum number of cells reached ($this->maxCellsNumber)", E_USER_ERROR);
            }
            $this->currentCell++;
            //$this->currentCell+= $this->repeatCell; // Default value of $repeatCell = 1
            $this->currentFrame = 0;

            array_pop($this->contenttag_stack);

        } elseif ($cTagName == 'office:annotation') {
            array_pop($this->contenttag_stack);

            global $globaldata;
            $globaldata = "";

        } elseif ($cTagName == 'draw:frame') {
            $this->currentFrame++;
        } elseif ($cTagName == 'style:style') {

            $current_style = $this->currentStyle;

            $this->spreadsheet->styles[$current_style->name] = $current_style;

            unset($this->currentStyle);

        } elseif ($cTagName == 'number:number-style' ||
            $cTagName == 'number:currency-style' ||
            $cTagName == 'number:percentage-style'
        ) {
            $data_style = $this->currentDataStyle;

            $this->spreadsheet->formats[$data_style->id] = $data_style;

            unset($this->currentDataStyle);

            // Pop 2 times (prefix & suffix)
            array_pop($this->contenttag_stack);
            array_pop($this->contenttag_stack);
        } elseif ($cTagName == 'text:p') {
            $this->characterData($parser, '</p>');

        } elseif ($cTagName == 'text:span') {
            $this->characterData($parser, '</span>');

        }
    }

    function characterData($parser, $data)
    {
        // Permet de prendre en charge les chaines de caracètres coupées par le parser
        global $globaldata;

        if ($this->lastElement == 'number:currency-symbol' && $data != "$") {
            // Escape currency symbol different from "$"
            $data = "";
        }

        // Filters HTML tags
        $escape_tags = array('<hr>', '&nbsp;', "</span>", "</p>");
        $data = self::html_filter($data, $escape_tags);

        if ($globaldata != "") {
            $globaldata = $globaldata . $data;
        } else {
            $globaldata = $data;
        }

        $c_container = (!empty($this->contenttag_stack)) ?
            $c_container = self::endc($this->contenttag_stack) : "";

        if ($c_container == 'table:table-cell') {

            for ($i = 0; $i < $this->repeatCell; $i++) {
                // La cellule courante et toutes les n-1 cellules précédentes si répétition de n on affecte la valeur
                $this->sheets[$this->currentSheet]['rows'][$this->currentRow]['cells'][$this->currentCell - $i]['value'] = $globaldata;
            }

        } elseif ($c_container == "office:annotation") {

            $this->sheets[$this->currentSheet]['rows'][$this->currentRow]['cells'][$this->currentCell]['annotation'] = $globaldata;

        } elseif ($c_container == 'data-style-prefix') {

            $this->currentDataStyle->data_style_set_prefix($globaldata);
        } elseif ($c_container == 'data-style-suffix') {

            $this->currentDataStyle->data_style_set_suffix($globaldata);
        }
    }

    function new_contenttag($content_tag)
    {
        // Add the new tag to the stack
        $this->contenttag_stack[] = $content_tag;
        // Clean globaldata
        global $globaldata;
        $globaldata = "";
    }

    function getColDefaultCellStyle($sheet, $col)
    {
        /*
        if (array_key_exists($sheet, $this->sheets))
            if (array_key_exists($col, $this->sheets[$sheet]['column']))
                if (array_key_exists("TABLE:DEFAULT-CELL-STYLE-NAME", $this->sheets[$sheet]['column'][$col]['attrs']))
                    return $this->sheets[$sheet]['column'][$col]['attrs']["TABLE:DEFAULT-CELL-STYLE-NAME"];
        */
    }

    static function html_filter($str_in, $escape_tags = array())
    {

        $tag = array_shift($escape_tags);

        if ($tag) {
            $peaces = explode($tag, $str_in);
            foreach ($peaces as $key => $peace) {
                $peaces[$key] = self::html_filter($peace, $escape_tags);
            }
            $str_out = implode($tag, $peaces);
        } else {
            $str_out = htmlentities($str_in, ENT_QUOTES, "UTF-8");
        }

        return $str_out;
    }

    static function endc($array)
    {
        return end($array);
    }
}
