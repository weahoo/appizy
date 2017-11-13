<?php

namespace Appizy;

use Appizy\Cell;
use PHPUnit\Framework\TestCase;

class CellTest extends TestCase
{
    public function testGetValueWithValue()
    {
        $cell = new Cell(0, 0, 0);
        $cell->setValueAttr(42);

        $this->assertEquals(42, $cell->getValue());
    }

    public function testGetValueWithDisplayedValue()
    {
        $cell = new Cell(0, 0, 0);
        $cell->setDisplayedValue("42");

        $this->assertEquals("42", $cell->getValue());
    }

    public function testGetValueWithValueAndDisplayedValue()
    {
        $cell = new Cell(0, 0, 0);
        $cell->setValueAttr("42");
        $cell->setDisplayedValue("$42.00");

        $this->assertEquals("42", $cell->getValue());
    }

    public function testGetValueWithValueBeing0AndDisplayedValue()
    {
        $cell = new Cell(0, 0, 0);
        $cell->setValueAttr("0");
        $cell->setDisplayedValue("<p>$0.00</p>");

        $this->assertEquals("0", $cell->getValue());
    }

    public function testGetValueShouldReturnDecodedHTML()
    {
        $cell = new Cell(0, 0, 0);
        $cell->setValueAttr("you &amp; me");

        $this->assertEquals("you & me", $cell->getValue());
    }
}
