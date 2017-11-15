<?php

namespace Appizy;

use PHPUnit\Framework\TestCase;

class ODSReaderTest extends TestCase
{
    public function testODS()
    {
        $ods = new \Appizy\ODSReader();
        $ods->load(__DIR__ . '/fixtures/demo-appizy.ods');

        $tables = $ods->getTables();
        $this->assertEquals(10, $tables->length);
    }

    public function testGetRows()
    {
        $ods = new \Appizy\ODSReader();
        $ods->load(__DIR__ . '/fixtures/demo-appizy.ods');

        $tables = $ods->getTables();

        $firstTabRows = $ods->getRows($tables->item(0));
        $this->assertEquals(30, $firstTabRows->length);
    }

    public function testGetCells()
    {
        $ods = new \Appizy\ODSReader();
        $ods->load(__DIR__ . '/fixtures/demo-appizy.ods');

        $tables = $ods->getTables();

        $firstTabRows = $ods->getRows($tables->item(0));
        $firstRowCells = $ods->getCells($firstTabRows->item(0));
        $this->assertEquals(2, $firstRowCells->length);
    }
}
