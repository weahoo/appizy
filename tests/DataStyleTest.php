<?php

use Appizy\DataStyle;


class DataStyleTest extends PHPUnit_Framework_TestCase
{
    public function testStringFormatterDoesNotTakeMinDigitIntoAccount()
    {
        $dataStyle = new DataStyle(0);
        $dataStyle->setMinIntDigit(1);
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
        $dataStyle->setSuffix('%');
        $this->assertEquals($dataStyle->toNumeralStringFormat(), '0%');
    }

    public function testFormatWithMinIntDigit()
    {
        $dataStyle = new DataStyle(0);
        $dataStyle->setMinIntDigit(2);
        $dataStyle->setDecimalPlaces(2);
        $this->assertEquals($dataStyle->toNumeralStringFormat(), '0.00');
    }

    public function testFormatCodeWithEmptyMinIntDigit()
    {
        $dataStyle = new DataStyle(0);
        $dataStyle->setDecimalPlaces(3);
        $this->assertEquals($dataStyle->toNumeralStringFormat(), '0.000');
    }

    public function testFormatCodeWithGrouping()
    {
        $dataStyle = new DataStyle(0);
        $dataStyle->setGrouping(true);
        $dataStyle->setDecimalPlaces(2);
        $this->assertEquals($dataStyle->toNumeralStringFormat(), '0,0.00');
    }
}
