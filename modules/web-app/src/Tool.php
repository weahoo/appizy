<?php

namespace Appizy\WebApp;

class Tool
{
    use ArrayTrait;

    var $sheets = array();
    var $styles = array();
    var $formulas = array();
    var $validations = array();
    var $formats = array();

    // Temporary fix
    var $used_styles = array();

    private $debug;
    private $error;

    function __construct($debug = false)
    {
        $this->debug = $debug;
    }

    /**
     * Parses an XML workbook into tool object
     */
    function tool_parse_wb($xml_path)
    {

        $extracted_ods = new OpenDocumentParser($xml_path, $this->debug);

        $this->sheets = $extracted_ods->sheets;
        $this->formulas = $extracted_ods->formulas;
        $this->styles = $extracted_ods->styles;
        $this->validations = $extracted_ods->validations;
        $this->formats = $extracted_ods->formats;

        $this->used_styles = $extracted_ods->used_styles;

        // Set formulas dep as "in"
        $this->formula_dep_setin();
        /**
         * Styles cleaning
         */
        foreach ($this->styles as $id => $style) {

            $parent_style_name = $style->parent_style_name;

            if ($parent_style_name != '') {

                $parent_style = $this->styles[$parent_style_name];

                $style->style_merge($parent_style);

                $this->styles[$id] = $style;
            }
        }
    }

    /**
     * Sets all formula dependancies as type "input"
     */
    function formula_dep_setin()
    {

        $index = 0;
        foreach ($this->formulas as $formula) {


            if ($formula->formula_isprintable()) {

                $dependances = $formula->get_dependances();

                foreach ($dependances as $dep) {

                    $tempcell = $this->tool_get_cell($dep[0], $dep[1], $dep[2]);
                    if ($tempcell) {
                        // If cell exists
                        if ($tempcell->getType() != 'out')
                            // Si elle n'est pas une formule alors est devient "in"
                            $tempcell->cell_set_type('in');
                    } else {
                        // If cell doesn't exists
                        // Happens when formula's ranges are on empty cells
                        $new_cell = new cell($dep[0], $dep[1], $dep[2], array("type" => 'in'));
                        //$this->tool_get_row($dep[0],$dep[1]);
                    }
                }
            }
            // $index++;
        }
    }

    function sheets_name()
    {
        $names = array();
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
            $temp_validation = str_replace('$', '', $validation['attrs']['TABLE:CONDITION']);
            $temp_validation_pieces = explode(":", $temp_validation, 2);
            $temp_validation = $temp_validation_pieces[1];
        } else {
            $temp_validation = "";
        }

        if (preg_match('/cell-content-is-in-list/', $temp_validation)) {
            // si validation de type "list de valeurs"

            preg_match_all("/cell-content-is-in-list\((.*)\)/", $temp_validation, $matches);
            $values = $matches[1][0];

            //$this->tool_debug("Validation: ".$values."");

            $temp_car = str_split($values);

            if ($temp_car[0] == '[') {
                // Range de valeurs


                $values = str_replace(array('[', ']'), '', $values);
                $values = explode(":", $values, 2);

                $head = string2coord($values[0], 0, $sheets_name);

                $tail = string2coord($values[1], $head[0], $sheets_name);

                $values = array();
                for ($i = 0; $i <= $tail[1] - $head[1]; $i++) {
                    for ($j = 0; $j <= $tail[2] - $head[2]; $j++) {

                        $tmp_sI = $head[0];
                        $tmp_rI = $head[1] + $i;
                        $tmp_cI = $head[2] + $j;

                        $tempcell = $this->tool_get_cell($tmp_sI, $tmp_rI, $tmp_cI);

                        if ($tempcell) $values[] = $tempcell->cell_get_value();
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
                $head = string2coord($validation['attrs']['TABLE:BASE-CELL-ADDRESS'], 0, $sheets_name);
                $tmp_sI = $head[0];
                $tmp_rI = $head[1];
                $tmp_cI = $head[2];
            }
            $tempcell = $this->sheets[$tmp_sI]->row[$tmp_rI]->cell[$tmp_cI];
            $tempcell->setValueInList($values);
        }
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

    /**
     * Sets error message for the class (used in case a function returns FALSE)
     */
    function tool_error($message)
    {
        trigger_error(__CLASS__ . '-' . __FUNCTION__ . ': ' . $message, E_USER_WARNING);
        //$this->error = $message;
    }

    /**
     * Trigger an error message in host log
     */
    function tool_debug($message)
    {
        trigger_error(__CLASS__ . ': ' . $message);
    }

    // Nettoie un tableau des feuilles, lignes et colonnes vides
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
        if ($offset > 0) $sheets_reverse = array_slice($sheets_reverse, $offset);
        // On inverse a nouveau et on affecte les sheets du tableau
        $sheets = array_reverse($sheets_reverse, true);
        $this->sheets = $sheets;

    }

    function tool_render_bs_mono()
    {

        $sections = array();
        $index = 0;
        $is_first = true;
        foreach ($this->sheets as $sheet) {

            if ($is_first) {
                $elements['front_page'] = $sheet->get_id();
                $elements['title'] = $sheet->get_sheet_name();
                $sections[$sheet->get_id()] = "Home";
            } else {
                $sections[$sheet->get_id()] = $sheet->get_sheet_name();
            }
            $is_first = false;
            $index++;
        }

        // Navigation construction
        $elements['sections'] = $sections;
        $elements['navid'] = "affix-nav";

        $html = appizy_render('bs-navigation.tpl.php', $elements);


        $is_first = true;
        foreach ($this->sheets as $sheet) {
            $html .= bs_grid($sheet, $is_first);

            $is_first = false;

            // Detects column common styles names
            $cols = $sheet->sheet_get_cols();

            $is_first_col = true;
            foreach ($cols as $col) {
                $tmp_styles = $col->col_get_default_cell_style();

                if ($is_first_col) {
                    $common_styles = $tmp_styles;
                } else {
                    $common_styles = ($tmp_styles == $common_styles) ? $tmp_styles : '';
                }
            }
            if ($common_styles != '' && $common_styles != "Default") {

                $section_style = new style('appizy-section-' . $sheet->get_id());
                $section_style->styles = $this->styles[$common_styles]->styles;

                $this->styles[] = $section_style;
            }
        }

        // CSS construction
        $css = "";
        foreach ($this->styles as $style) {
            $css .= $style->style_print(array('width', 'height'));
        }

        $css .= ".appizy-section-first,
.appizy-section { padding: 100px 0;}" . "\n";
        $css .= ".appizy-section { border-bottom: 1px solid #E5E5E5; }";
        $css .= ".appizy-section h1{ padding: 10px 0 20px; font-weight:200; text-align:center;}" . "\n";

        $script = "var navOffset = parseInt($('body').css('padding-top'));
$('body').scrollspy({ target: '#affix-nav', offset: navOffset+10 });
$('.navbar a').click(function (event) {
  var scrollPos = jQuery('body').find($(this).attr('href')).offset().top - navOffset;
  $('body,html').animate({ scrollTop: scrollPos}, 500, function () {});
  return false;
});" . "\n";


        $variables['style'] = $css;
        $variables['content'] = $html;
        $variables['script'] = $script;


        return $variables;
    }

    /**
     * Renders Tool as Affix JS
     */
    function tool_render_bs_grid()
    {
        $html = '';
        $nl = "\n";
        $variables = array();


        foreach ($this->sheets as $sheet) {

            $max_length = 0;

            $html .= '<h1>' . $sheet->get_sheet_name() . '</h1>';

            foreach ($sheet->row as $row) {
                // First loop, check the longest row
                $row_length = $row->row_length();
                $max_length = $row_length > $max_length ? $row_length : $max_length;
            }

            // Calculates the size of column in the Bootstrap grid
            $col_size = floor(12 / $max_length);

            foreach ($sheet->row as $row) {
                $row_class = '';
                $row_style = $row->get_styles_name();
                // if($row_style != '') $row_class .= " ".$row_style;

                $html .= '<div class="row">';
                // Second loop, construct HTML table
                $cells = $row->row_get_cells();
                foreach ($cells as $cell) {
                    $cell_size = $cell->cell_get_colspan();
                    $cell_value = $cell->cell_get_value();

                    // Ajout du style de la cellule
                    $cell_class = '';
                    $cell_style = $cell->get_styles_name();
                    if ($cell_style != '') $cell_class .= " " . $cell_style;

                    $html .= '<div class="col-md-' . ($cell_size * $col_size) . $cell_class . $row_class . '"><p>' . $cell_value . '</p></div>' . $nl;
                }
                $html .= '</div>';
            }
        }

        $css = "";
        foreach ($this->styles as $style) {
            $css .= $style->style_print(array('width'));
        }
        //$css .= '.row > div { height:inherit; }'.$nl;

        $variables['style'] = $css;
        $variables['content'] = $html;

        //$variables['script'] = $script;

        return $variables;
    }

    /**
     * Renders Tool as Affix JS
     */
    function tool_render_bs_affix()
    {
        $html = '';
        $nl = "\n";

        $affix_index = 0;
        $affix_nav = array();
        $affix_content = array();

        foreach ($this->sheets as $sheet) {
            foreach ($sheet->row as $row) {
                $temp_nav_cell = $row->row_get_cell(0);
                if ($temp_nav_cell && $temp_nav_cell->cell_get_value() != '') {
                    $affix_nav[$affix_index] = $temp_nav_cell->cell_get_value();

                    // Search for content if only we have an nav value
                    $temp_content_cell = $row->row_get_cell(1);

                    $affix_content[$affix_index] = $temp_content_cell ?
                        $temp_content_cell->cell_get_value() : '';

                    $affix_index++;
                }
            }
        }

        // CSS
        $style = '.sidenav > .active > a { border-left: 2px solid;}' . $nl;
        // Javascript
        //$script = "jQuery('body').scrollspy({ target: '#affix-nav' });".$nl;
        $script = "var navOffset = parseInt($('body').css('padding-top'));
$('body').scrollspy({ target: '#affix-nav', offset: navOffset+10 });
$('li a').click(function (event) {
  var scrollPos = jQuery('body').find($(this).attr('href')).offset().top - navOffset;
  $('body,html').animate({ scrollTop: scrollPos}, 500, function () {});
  return false;
});" . "\n";
        // HTML prefix
        $html .= '<div class="row">' . $nl;

        // Renders navigation
        $html .= '<div id="affix-nav" class="sidebar col-md-4">' . $nl;
        $html .= '<ul class="nav sidenav" data-spy="affix">' . $nl;
        foreach ($affix_nav as $nav_index => $nav) {
            $html .= '  <li><a href="#affix-section-' . $nav_index . '" >' . $nav . '</a></li>' . $nl;
        }
        $html .= '</ul>' . $nl;
        $html .= '</div>' . $nl;

        // Renders content
        $html .= '<div id="content" class="col-md-8">' . $nl;
        foreach ($affix_content as $content_index => $content) {
            $html .= '<div id="affix-section-' . $content_index . '">' . $nl;
            $html .= '<h1>' . $affix_nav[$content_index] . '</h1>' . $nl;
            $html .= $content . $nl;
            $html .= '</div>' . $nl;
        }
        $html .= '</div>' . $nl;

        // HTML suffix
        $html .= '</div><!-- end of row -->' . $nl;

        $variables['style'] = $style;
        $variables['content'] = $html;
        $variables['script'] = $script;

        return $variables;
    }

    /**
     * Renders HTML of a tool
     */
    function tool_render($pathfile = null, $level = 0, $options = array())
    {

        // Debug
        $option_print_header = array_key_exists("print header", $options) ?
            $options['print header'] == true : false;
        $option_comptact_css = array_key_exists("compact css", $options) ?
            $options['compact css'] == true : false;
        $option_jquery_tab = array_key_exists("jquery tab", $options) ?
            $options['jquery tab'] == true : false;
        $option_freeze = array_key_exists("freeze", $options) ?
            $options['freeze'] : array();

        $option_freeze_string = in_array("string", $option_freeze, true);
        $option_freeze_num = in_array("num", $option_freeze, true);

        $text = "";

        // Pour le script des formules
        $script = "";
        $libraries = [];
        $stylesheets = [];

        // Default assets for webapplication
        $libraries = [
            'jquery' => '<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>' . "\n",
            'jqueryui' => '<script src="http://code.jquery.com/ui/1.10.4/jquery-ui.min.js"></script>' . "\n",
            'numeral' => '<script src="http://cdnjs.cloudflare.com/ajax/libs/numeral.js/1.5.3/numeral.min.js"></script>' . "\n"
        ];


        if ($option_jquery_tab) {
            $libraries['jqueryui-theme-smoothness'] = '<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">' . "\n";
            $script .= 'jQuery( "#sheets" ).tabs();' . "\n";
            $script .= 'jQuery( "#sheets" ).tooltip();' . "\n";
        }

        $formulas = "// Cells formulas" . "\n";
        $formulaslist = array();

        $countsheet = 0;

        $htmlTable = '';

        $sheets_link = array(); // Array containing link to sheet anchors
        $used_styles = array(); // Contains styles used by the table elements.

        foreach ($this->sheets as $key => $sheet) {

            // For each sheet in the table
            $sheet_row_number = count($sheet->row);

            $sheet_id = "sheet-" . $countsheet;
            $sheet_name = $sheet->getName();

            // Creates the link to the anchor
            $sheets_link[] = '<a href="#' . $sheet_id . '">' . $sheet_name . '</a>';

            $htmlTable .= '<div id="' . $sheet_id . '">' . "\n" .
                '<table>' . "\n" .
                '<tbody>' . "\n";

            foreach ($sheet->row as $row_index => $row) {
                $row_cell_number = $row->row_nbcell();


                $rowstyle = "";
                $rowstyle = ' class="' . $row->getName() . ' ' . $row->get_styles_name() . '"';

                $used_styles[] = $row->get_styles_name();

                if ($row->collapse) $rowstyle .= ' style="visibility:collapse"';

                $htmlTable .= '<tr' . $rowstyle . '>' . "\n";

                foreach ($row->row_get_cells() as $cCI => $tempcell) {

                    if ($tempcell->cell_get_validation() != '') {
                        $this->render_validation($tempcell->cell_get_validation(), array($key, $row_index, $cCI));
                        $tempcell->cell_set_type("in");
                    }

                    $td = "";

                    // Ajout du style de la cellule
                    $tempstyle = '';
                    $tempstyle = $tempcell->get_styles_name();

                    $used_styles[] = $tempstyle;

                    $data_format = "";

                    if ($tempstyle != '' && $tempstyle != 'Default') {

                        $data_style = self::array_attribute($this->styles, $tempstyle);

                        if ($data_style != '') {

                            $data_style_name = $data_style->data_style_name;

                            $parent_style_name = $data_style->parent_style_name;
                            $parent_style_data_style_name = '';

                            $data_style_name = ($data_style_name != "") ?
                                $data_style_name : $parent_style_data_style_name;

                            if ($data_style_name != '') {

                                $main_data_format = self::array_attribute($this->formats, $data_style_name);

                                if ($main_data_format != '' && $main_data_format != 'N0') {

                                    $data_format = $main_data_format->format_code();
                                    //if ($data_format == "0.00   ") dpm($main_data_format);

                                    if (!empty($main_data_format->maps)) {
                                        foreach ($main_data_format->maps as $condition => $map) {
                                            if ($condition == 'value()>=0') {
                                                if ($map_format = self::array_attribute($this->formats, $map))
                                                    $data_format .= ';' . $map_format->format_code();
                                            }
                                        }
                                    }

                                    $data_format = 'data-format="' . $data_format . '" ';
                                }
                            }
                        }

                    }
                    $value_type = $tempcell->cell_value_type();

                    switch ($tempcell->getType()) {
                        case 'in':
                            $class = "in";
                            $list_values = $tempcell->getValueList();
                            if (empty($list_values)) {
                                $disabled = (
                                    ($option_freeze_string && $value_type == 'string') ||
                                    ($option_freeze_num && $value_type == 'float')
                                ) ? "disabled " : "";
                                $td .= '<input data-type="' . $value_type . '" ' . $data_format . $disabled . ' id="' . $tempcell->getName() . '" name="' . $tempcell->getName() . '" type="text" value="' . $tempcell->cell_get_value() . '">';
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
                    if ($tempstyle != '') $class .= " " . $tempstyle;

                    // Adds current col style (if exists)
                    $temp_curcol_style = '';
                    $temp_curcol = $sheet->getCol($cCI);


                    if ($temp_curcol) {
                        $temp_curcol_style = $temp_curcol->get_styles_name();
                        if ($temp_curcol_style != '') $class .= " " . $temp_curcol_style;
                        $used_styles[] = $temp_curcol_style;

                        // Hidde cell if col is collapsed
                        if ($temp_curcol->get_collapsed() == true) $class .= " hidden-cell";
                    }
                    // Gestion du Colspan
                    $colspan = $tempcell->cell_get_colspan();
                    $htmlcolspan = '';
                    if ($colspan > 1) $htmlcolspan = ' colspan="' . $colspan . '"';

                    // Gestion du Rowspan
                    $rowspan = $tempcell->cell_get_rowspan();
                    $htmlrowspan = '';
                    if ($rowspan > 1) $htmlrowspan = ' rowspan="' . $rowspan . '"';

                    // Gestion des annotations
                    $annotation = $tempcell->cell_get_annotation();
                    if ($annotation != '') $annotation = ' title="' . $annotation . '"';

                    // Ajout de la cellule g�n�r�e dans le tableau
                    $htmlTable .= '  <td' . $htmlcolspan . $htmlrowspan . $annotation . ' class="' . $class . '">' . $td . '</td>' . "\n";
                } // End foreach cell

                // Close current row tag
                $htmlTable .= "</tr>" . "\n";
            }

            // Close current sheet tags
            $htmlTable .= '</tbody>' . "\n";
            $htmlTable .= '</table>' . "\n";
            $htmlTable .= '</div><!-- /#' . $sheet_id . '-->' . "\n";

            $countsheet++;
        } // End foreach sheet
        $htmlTable .= "</div><!-- /#sheets -->" . "\n";

        // Create the list of links to the sheet (jQuery compatible)
        $list_link = "<ul>" . "\n";
        foreach ($sheets_link as $link) {
            $list_link .= "  <li>" . $link . "</li>" . "\n";
        }
        $list_link .= "</ul>" . "\n";

        $htmlTable = $list_link . $htmlTable;
        $htmlTable = '<div id="sheets">' . $htmlTable;


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
        $steps = array(); // Array des steps de calcul

        // Variables uniquement pour la boucle while qui suit :
        $ext_formulas = array();

        $count_formula = 0;
        $count_nonprintedformula = 0;

        foreach ($this->formulas as $formula) {
            if ($level == 1) {
                $dependances = array();
                foreach ($formula->get_dependances() as $dependance) {
                    $dependances[] = 's' . $dependance[0] . 'r' . $dependance[1] . 'c' . $dependance[2];
                }

                $formulas .= $formula->get_script() . "\n";
                $formulaslist[$formula->get_name()] = array('call' => $formula->get_call(),
                    'dep' => $dependances,
                );
                foreach ($formula->get_ext_formula() as $ext_formula) {
                    $ext_formulas[] = $ext_formula;
                }
                $count_formula++;
            } else {
                $count_nonprintedformula++;
            }
        }
        $ext_formulas = array_unique($ext_formulas);


        $formulaslist_copy = $formulaslist; // $formulalist est copi� car nous allons avoir besoin de triturer cet Array
        $currentstep = 0; // Step de calcul en cours
        $fomulas_unclassified = count($formulaslist); // D�compte de formules qu'il reste � classer

        $fomulas_unclassified_laststep = 0; // Variable de sortie de la boucle while. Si jamais la boucle n'�limine pas de formule � la step...

        // Pour le log

        // appizy_logapp("");
        // appizy_logapp("Number of formulas:".$fomulas_unclassified);

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

                        array_splice($formulaslist_copy[$formcell]['dep'], $offsetdep, 1);
                        // appizy_logapp("Dep calculee $value");
                    } else {
                        /*
                         * La formule d�pend d'une cellule qui fait partie de la liste des formules � calculer
                         * On incr�mente alors l'offset de d�pendance. La formule sera calcul�e � l'�tape n+1
                         */
                        //echo $formcell."Offsetdep:".$offsetdep."-Maxdep:".$nbdep."<br>";
                        $offsetdep++;
                        // appizy_logapp("Dep pas calculee $value");
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
                    array_push($steps[$currentstep]['formulas'], $temp_formula['call']);

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
            array_walk_recursive($currentstep['dep'], function ($a) use (&$flat_stepdep) {
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
                //'function step'.$currentstep_index.'('.$stepdep.')'."\n".'{'."\n";

                foreach ($currentstep['formulas'] as $formula) {
                    $formulascall .= $formula;
                }
                $formulascall .= "\n" . '}' . "\n";
            }
        } else {
            $run_calc = "";
            $formulascall = "";
        }
        $run_calc .= "};" . "\n";

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
                $formulas_ext .= $this->getExtFunction($formula, __DIR__ . "/../assets/js/src/appizy.js");
            }

            foreach ($ext_formulas as $ext_formula) {
                $formulas_ext .= $this->getExtFunction($ext_formula, __DIR__ . "/../assets/js/src/formula.js");
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

        /*
        $cssTable = "\n".'<style type="text/css">'."\n"
                                .$this->tool_get_css($used_styles, $option_comptact_css)
                                .'</style>'."\n";
                                */
        $cssTable = $this->tool_get_css($used_styles, $option_comptact_css);

        // *** R�union des diff�rents morceaux
        // $htmlTable = $cssTable.$htmlTable;

        //$variables['style'] = $style;
        $variables['content'] = $htmlTable;
        $variables['style'] = $cssTable;
        $variables['script'] = $script;
        $variables['libraries'] = $libraries;

        return $variables;

    }

    /**
     * Return CSS code of the $used_styles in the tool + default CSS code
     *
     * Note: a tool might have more styles that come from the parsed XML, but are
     * not used into by the element of it.
     */
    function tool_get_css($used_styles = array(), $compact_code = true)
    {

        $css_code = file_get_contents(__DIR__ . "/../assets/css/style-webapp-default.css");

        $used_styles = array_flip($used_styles);
        // Gets intersection of used and available styles
        $used_styles = array_intersect_key($this->styles, $used_styles);

        foreach ($used_styles as $key => $value) $css_code .= $value->style_print();

        if ($compact_code) $css_code = compact_code($css_code);

        return $css_code . "\n";
    }

    /**
     * Gets functions out of external library
     *
     * @param string $function_name
     * @param string $library_path
     * @param array $already_loaded
     * @return string
     */
    function getExtFunction($function_name, $library_path, $already_loaded = [])
    {
        $namu = $function_name;
        $function_name = preg_quote($function_name);

        $ext_formula = ""; // Default returned value

        $expression = "/" . $function_name . " = function(.*?)\};/is";

        if (!in_array($function_name, $already_loaded)) {

            if (preg_match_all($expression, file_get_contents($library_path), $match)) {
                $function = $match[1][0];
                $ext_formula = $namu . " = function" . $function . "};" . "\n\n";
            }

            $already_loaded[] = $namu;

            // Chargement des d�pendances de la fonction
            if (preg_match_all("/Formula.(.*?)\(/is", $function, $match)) {
                // Si la fonction a des d�pendances
                $match = array_unique($match[1]); // Chaque d�pendance n'est imprim�e qu'une fois
                //$match = array_diff($already_loaded,$match);
                foreach ($match as $dep_name) {

                    $dep_name = "Formula." . $dep_name;

                    if (!in_array($dep_name, $already_loaded))
                        $ext_formula .= $this->getExtFunction($dep_name, $library_path, $already_loaded);
                }
            }
        }

        return $ext_formula;
    }
}
