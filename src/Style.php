<?php

namespace Appizy;

class Style
{
    /** @var string */
    protected $name;
    // Array contenant les donn�es de style pour le texte
    protected $styles;
    // Reference to an existing data-style
    protected $data_style_name;
    /** @var  string */
    protected $parent_style_name;

    public function __construct($myName)
    {
        $this->name = $myName;
        $this->styles = array();
    }

    /**
     * @return array
     */
    public function getStyles()
    {
        return $this->styles;
    }

    /**
     * @param array $styles
     */
    public function setStyles($styles)
    {
        $this->styles = $styles;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getDataStyleName()
    {
        return $this->data_style_name;
    }

    /**
     * @param mixed $data_style_name
     */
    public function setDataStyleName($data_style_name)
    {
        $this->data_style_name = $data_style_name;
    }

    /**
     * @return string
     */
    public function getParentStyleName()
    {
        return $this->parent_style_name;
    }

    /**
     * @param string $parent_style_name
     */
    public function setParentStyleName($parent_style_name)
    {
        $this->parent_style_name = $parent_style_name;
    }

    /**
     * Merges the another Style into the Style. Option: overriding existing properties
     * @param Style $style
     * @param bool  $override
     */
    public function styleMerge($style, $override = false)
    {
        $style_data_style_name = $style->data_style_name;

        // Merges data style name
        if ($override && $style_data_style_name != ''
            || $this->data_style_name == ''
        ) {
            // If data style is empty OR override is on and new value
            $this->data_style_name = $style_data_style_name;
        }

        // Merges properties
        if ($override) {
            $this->styles = array_merge($this->styles, $style->styles);
        } else {
            $this->styles = array_merge($style->styles, $this->styles);
        }
    }

    /**
     * Renvoi le code CSS de l'objet style
     */
    public function printStyle()
    {
        $name = $this->name;
        $styleCode = '.' . $name . "\n" . '{' . "\n";

        $prop = $this->styles;

        if (is_array($prop)) {
            foreach ($prop as $key => $value) {
                $styleCode .= '    ' . $key . ':' . $value . ';' . "\n";
            }

            $styleCode .= '}' . "\n";

            return $styleCode;
        } else {
            return false;
        }
    }

    /**
     * Return CSS Code of the Style
     * @param array $exclude
     * @return string
     */
    public function getCssCode($exclude = array())
    {
        $name = $this->name;
        $style_code = '';

        $prop = $this->styles;

        $exclude = array_flip($exclude);

        // Certains styles n'ont pas de propri�t�s
        if (is_array($prop)) {
            $css_properties = "";
            foreach ($prop as $key => $value) {
                if (!array_key_exists($key, $exclude)) {
                    // If the key is not excluded
                    $css_properties .= '    ' . $key . ':' . $value . ';' . "\n";
                }
            }
            // If they are some properties, creates style code
            if ($css_properties != '') {
                $style_code = '.' . $name . ' { ' . "\n" . $css_properties . ' }' . "\n";
            }
        }

        return $style_code;
    }

    /**
     * @param $myOdsStyles
     */
    public function addOdsStyles($myOdsStyles)
    {
        $i = 0;
        foreach ($myOdsStyles as $key => $value) {
            $propName = '';
            $propValue = '';

            switch ($key) {
                case 'FO:FONT-WEIGHT':
                    $propName = 'font-weight';
                    $propValue = $value;
                    break;
                case 'FO:FONT-STYLE':
                    $propName = "font-style";
                    $propValue = $value;
                    break;
                case 'FO:BACKGROUND-COLOR':
                    $propName = "background-color";
                    $propValue = $value;
                    break;
                case 'FO:TEXT-ALIGN':
                    $propName = "text-align";
                    $propValue = $value;
                    break;
                case 'FO:BORDER-TOP':
                    $propName = "border-top";
                    $propValue = $value;
                    break;
                case 'FO:BORDER-RIGHT':
                    $propName = "border-right";
                    $propValue = $value;
                    break;
                case 'FO:BORDER-BOTTOM':
                    $propName = "border-bottom";
                    $propValue = $value;
                    break;
                case 'FO:BORDER-LEFT':
                    $propName = "border-left";
                    $propValue = $value;
                    break;
                case 'FO:BORDER':
                    $propName = "border";
                    $propValue = $value;
                    break;
                case 'FO:COLOR':
                    $propName = "color";
                    $propValue = $value;
                    break;
                case 'FO:FONT-SIZE':
                    $propName = "font-size";
                    $propValue = $value;
                    break;
                case 'STYLE:ROW-HEIGHT':
                    $propName = "height";
                    $propValue = $value;
                    break;
                case 'STYLE:COLUMN-WIDTH':
                    $propName = "width";
                    $propValue = $value;
                    break;
                case "STYLE:FONT-NAME":
                    $propName = "font-family";
                    $propValue = $value;
                    if ($value == 'Arial1') {
                        $propValue = "Arial";
                    }
                    if ($value == 'Arial2') {
                        $propValue = "Arial";
                    }
                    if ($value == 'Arial3') {
                        $propValue = "Arial";
                    }
                    break;
                case "STYLE:TEXT-UNDERLINE-STYLE":
                    if ($value != 'none') {
                        $propName = "text-decoration";
                        $propValue = "underline";
                    }
                    break;
                case "TABLE:DISPLAY":
                    if ($value === "false") {
                        $propName = "display";
                        $propValue = "none";
                    }
                    break;
            }

            if ($propName != '') {
                $cssStyles[$propName] = $propValue;
            }
            $i++;
        }

        if (isset($cssStyles)) {
            $this->addStyles($cssStyles);
        }
    }

    /**
     * @return bool
     */
    public function isShown()
    {
        if (array_key_exists('display', $this->styles)) {
            return !($this->styles['display'] === 'none');
        } else {
            return true;
        }
    }

    /**
     * @param $newStyles
     */
    public function addStyles($newStyles)
    {
        if (is_array($newStyles)) {
            foreach ($newStyles as $key => $value) {
                $this->styles[$key] = $value;
            }
        }
    }
}
