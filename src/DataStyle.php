<?php

namespace Appizy;

class DataStyle
{
    protected $id;
    /**
     * @var int
     */
    protected $decimalPlaces;
    /**
     * @var int
     */
    protected $minIntDigit;

    protected $maps;
    /**
     * @var string
     */
    protected $prefix;
    /**
     * @var string
     */
    protected $suffix;
    /**
     * @var boolean
     */
    protected $grouping;

    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getMinIntDigit()
    {
        return $this->minIntDigit;
    }

    /**
     * @param int $minIntDigit
     */
    public function setMinIntDigit($minIntDigit)
    {
        $this->minIntDigit = $minIntDigit;
    }

    /**
     * @return int
     */
    public function getDecimalPlaces()
    {
        return $this->decimalPlaces;
    }

    /**
     * @param int $decimalPlaces
     */
    public function setDecimalPlaces($decimalPlaces)
    {
        $this->decimalPlaces = $decimalPlaces;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        // Remove euro sign
        $prefix = str_replace(chr(0xE2) . chr(0x82) . chr(0xAC), '', $prefix);

        if ($prefix != ' ' && $prefix != '  ' && $prefix != '  ' && $prefix != '   ') {
            $this->prefix = $prefix;
        }
    }

    /**
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * @param string $suffix
     */
    public function setSuffix($suffix)
    {
        // Remove euro sign
        $suffix = str_replace(chr(0xE2) . chr(0x82) . chr(0xAC), '', $suffix);

        if ($suffix != ' ' && $suffix != '  ' && $suffix != '  ' && $suffix != '   ') {
            $this->suffix = $suffix;
        }
    }

    /**
     * @return bool
     */
    public function isGrouping()
    {
        return $this->grouping;
    }

    /**
     * @param bool $grouping
     */
    public function setGrouping($grouping)
    {
        $this->grouping = $grouping;
    }


    /**
     * @return mixed
     */
    public function getMaps()
    {
        return $this->maps;
    }

    /**
     * @param mixed $maps
     */
    public function setMaps($maps)
    {
        $this->maps = $maps;
    }

    /**
     * @param $mapIndex
     * @param $map
     */
    public function setMap($mapIndex, $map)
    {
        $this->maps[$mapIndex] = $map;
    }

    /**
     * @return string
     */
    public function toNumeralStringFormat()
    {
        $code = '';

        if ($this->grouping) {
            $code .= '0,0';
        } else {
            if ($this->decimalPlaces > 0 || isset($this->suffix) || isset($this->prefix)) {
                $code .= '0';
            }
        }

        $isFirst = true;
        for ($i = 0; $i < $this->decimalPlaces; $i++) {
            $code .= ($isFirst) ? '.' : '';
            $code .= '0';
            $isFirst = false;
        }

        return $this->prefix . $code . $this->suffix;
    }
}
