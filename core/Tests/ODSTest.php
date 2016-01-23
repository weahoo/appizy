<?php

class OdsTest extends PHPUnit_Framework_TestCase
{
    public function testODS()
    {
        $ods = new \Appizy\ODS();
        $ods->load(__DIR__ . '/Fixtures/demo-appizy.ods');

        $tables = $ods->getTables();
        $this->assertEquals(1, count($tables));

//        var_dump($ods);
        foreach ($tables as $table) {
//            var_dump($table);
            $newDOMDoc = new DOMDocument();
            $newDOMDoc->insertBefore($table);
//            $newDOMXPath = new DOMXPath($newDOMDoc);
//            $newDOMXPath->query('//table:table-row');
        }

    }
}
