<?php

namespace Appizy;

use Appizy\Parser\Parser;

class ODSReader
{
    /** @var \DOMXPath */
    private $xpath;

    public function __construct()
    {
    }

    public function load($file)
    {
        $this->xpath = Parser::parse($file);
    }

    /**
     * @return \DOMNodeList
     * TODO: put that into a Twig Extension. Does not belong to the ODS Object itself
     */
    public function getTables()
    {
        $xpath = $this->xpath;
        return $xpath->query('//table:table');
    }

    /**
     * @param \DOMNode $table
     * @return \DOMNodeList
     */
    public function getRows(\DOMNode $table = null)
    {
        $xpath = $this->xpath;
        return $xpath->query('.//table:table-row', $table);
    }

    /**
     * @param \DOMNode $row
     * @return \DOMNodeList
     */
    public function getCells(\DOMNode $row = null)
    {
        $xpath = $this->xpath;
        return $xpath->query('.//table:table-cell', $row);
    }
}
