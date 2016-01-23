<?php

namespace Appizy;

use Appizy\Parser;

class ODS
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
    public function getTables() {
        $xpath = $this->xpath;
        return $xpath->query('//table:table');
    }
} 