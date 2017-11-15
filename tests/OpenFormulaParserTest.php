<?php

namespace Appizy;

use Appizy\Parser\OpenFormulaParser;
use PHPUnit\Framework\TestCase;

class OpenFormulaParserTest extends TestCase
{

    public function testFormulaWithLegacyFunction()
    {
        $formula = OpenFormulaParser::parse('of:=LEGACY.FINV(0.05;-1)', 0, ['test'], [0, 0, 0]);
        $this->assertEquals($formula->getElements(), [
            'Formula.FINV',
            '(',
            '0.05',
            ',',
            '-',
            '1',
            ')'
        ]);
    }

    public function testDictionaryInGrowingTokenSize()
    {
        $formula = OpenFormulaParser::parse('of:=ROUND(ROUNDUP(ROUND(3.123)))', 0, ['test'], [0, 0, 0]);
        $this->assertEquals($formula->getElements(), [
            'Formula.ROUND',
            '(',
            'Formula.ROUNDUP',
            '(',
            'Formula.ROUND',
            '(',
            '3.123',
            ')',
            ')',
            ')',
        ]);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testThrowErrorForUnknownTokenFunction()
    {
        OpenFormulaParser::parse('of:=YOLO(4,5,"WEIRD FUNCTION SIGNATURE?")', 0, ['test'], [0, 0, 0]);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testThrowErrorForPowerTokenFunction()
    {
        OpenFormulaParser::parse('of:=2^3', 0, ['test'], [0, 0, 0]);
    }
}
