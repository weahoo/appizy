<?php

class ODSReaderTest extends PHPUnit_Framework_TestCase
{
    public function testODS()
    {
        $ods = new \Appizy\ODSReader();
        $ods->load(__DIR__ . '/fixtures/demo-appizy.ods');

        $tables = $ods->getTables();
        $this->assertEquals(6, $tables->length);
    }

    public function testGetRows() {
        $ods = new \Appizy\ODSReader();
        $ods->load(__DIR__ . '/fixtures/demo-appizy.ods');

        $tables = $ods->getTables();

        $firstTabRows = $ods->getRows($tables->item(0));
        $this->assertEquals(28, $firstTabRows->length);
    }

    public function testGetCells() {
        $ods = new \Appizy\ODSReader();
        $ods->load(__DIR__ . '/fixtures/demo-appizy.ods');

        $tables = $ods->getTables();

        $firstTabRows = $ods->getRows($tables->item(0));
        $firstRowCells = $ods->getCells($firstTabRows->item(0));
        $this->assertEquals(2, $firstRowCells->length);
    }
}
