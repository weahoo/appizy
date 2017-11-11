<?php

namespace Appizy;

class TableElement
{
    /**
     * @var integer
     */
    protected $eid;
    /**
     * @var string[]
     */
    protected $stylesNameList;

    public function __construct($element_id)
    {
        $this->setId($element_id);
        $this->stylesNameList = [];
    }

    public function setId($element_id)
    {
        $this->eid = (int) $element_id;
    }

    public function getId()
    {
        return $this->eid;
    }

    /**
     * @param string $styleName
     */
    public function addStyle($styleName)
    {
        $this->stylesNameList[] = $styleName;
    }

    /**
     * Returns style names concatenated
     *
     * @return string
     */
    public function getConcatStyleNames()
    {
        $styles_name = "";
        $is_first = true;
        foreach ($this->stylesNameList as $name) {
            $styles_name .= ($is_first) ? $name : " " . $name;
            $is_first = false;
        }

        return $styles_name;
    }

    /**
     * @return mixed
     */
    public function getStyleName()
    {
        return array_shift($this->stylesNameList);
    }

    /**
     * @return array
     */
    public function getStyles()
    {
        return $this->stylesNameList;
    }
}
