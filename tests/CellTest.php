<?php

use Appizy\Cell;

class CellTest extends PHPUnit_Framework_TestCase
{
    function testGetValueWithValue()
    {
        $cell = new Cell(0, 0, 0);
        $cell->setValueAttr(42);

        $this->assertEquals(42, $cell->getValue());
    }

    function testGetValueWithDisplayedValue()
    {
        $cell = new Cell(0, 0, 0);
        $cell->setDisplayedValue("42");


        var_dump($cell->getDisplayedValue(), isset($cell->value), $cell->value);

        $this->assertEquals("42", $cell->getValue());
    }

    function testGetValueWithValueAndDisplayedValue()
    {
        $cell = new Cell(0, 0, 0);
        $cell->setValueAttr("42");
        $cell->setDisplayedValue("$42.00");

        $this->assertEquals("42", $cell->getValue());
    }

    function testGetValueWithValueBeing0AndDisplayedValue()
    {
        $cell = new Cell(0, 0, 0);
        $cell->setValueAttr("0");
        $cell->setDisplayedValue("<p>$0.00</p>");

        $this->assertEquals("0", $cell->getValue());
    }

    function testGetValueShouldReturnDecodedHTML(){
        $cell = new Cell(0, 0, 0);
        $cell->setValueAttr("you &amp; me");

        $this->assertEquals("you & me", $cell->getValue());
    }
}
