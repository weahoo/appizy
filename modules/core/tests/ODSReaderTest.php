<?php

class ODSReaderTest extends PHPUnit_Framework_TestCase
{
    public function testODS()
    {
        $ods = new \Appizy\Core\ODSReader();
        $ods->load(__DIR__ . '/fixtures/demo-appizy.ods');

        $tables = $ods->getTables();
        $this->assertEquals(2, $tables->length);
    }

    public function testGetRows() {
        $ods = new \Appizy\Core\ODSReader();
        $ods->load(__DIR__ . '/fixtures/demo-appizy.ods');

        $tables = $ods->getTables();

        $firstTabRows = $ods->getRows($tables->item(0));
        $this->assertEquals(19, $firstTabRows->length);

        $allRows = $ods->getRows();
        $this->assertEquals(21, $allRows->length);
    }

    public function testGetCells() {
        $ods = new \Appizy\Core\ODSReader();
        $ods->load(__DIR__ . '/fixtures/demo-appizy.ods');

        $tables = $ods->getTables();

        $firstTabRows = $ods->getRows($tables->item(0));
        $firstRowCells = $ods->getCells($firstTabRows->item(0));
        $this->assertEquals(2, $firstRowCells->length);

        $allCells = $ods->getCells();
        $this->assertEquals(49, $allCells->length);
    }
}
