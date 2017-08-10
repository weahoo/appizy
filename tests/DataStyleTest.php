<?php

use Appizy\DataStyle;


class DataStyleTest extends PHPUnit_Framework_TestCase
{
    public function testStringFormatterDoesNotTakeMinDigitIntoAccount()
    {
        $dataStyle = new DataStyle(0);
        $dataStyle->minIntDigit = 1;
        $this->assertEquals($dataStyle->toNumeralStringFormat(), '');
    }

    public function testEmptyDataFormatIfNothingIsSet()
    {
        $dataStyle = new DataStyle(0);
        $this->assertEquals($dataStyle->toNumeralStringFormat(), '');
    }

    public function testFormatWithSuffixOnly()
    {
        $dataStyle = new DataStyle(0);
        $dataStyle->suffix = '%';
        $this->assertEquals($dataStyle->toNumeralStringFormat(), '0%');
    }

    public function testFormatWithMinIntDigit()
    {
        $dataStyle = new DataStyle(0);
        $dataStyle->minIntDigit = 2;
        $dataStyle->decimalPlaces = 2;
        $this->assertEquals($dataStyle->toNumeralStringFormat(), '0.00');
    }

    public function testFormatCodeWithEmptyMinIntDigit()
    {
        $dataStyle = new DataStyle(0);
        $dataStyle->decimalPlaces = 3;
        $this->assertEquals($dataStyle->toNumeralStringFormat(), '0.000');
    }

    public function testFormatCodeWithGrouping()
    {
        $dataStyle = new DataStyle(0);
        $dataStyle->grouping = true;
        $dataStyle->decimalPlaces = 2;
        $this->assertEquals($dataStyle->toNumeralStringFormat(), '0,0.00');
    }
}
