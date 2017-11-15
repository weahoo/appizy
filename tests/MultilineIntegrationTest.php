<?php

namespace Appizy;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Description of MultiLineIntegrationTest
 *
 * @author Usama Ahmed Khan
 */
class MultilineIntegrationTest extends TestCase
{
    
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        exec('bin/appizy convert tests/fixtures2/multiline-input.ods');
    }
    
    protected function setUp()
    {
        parent::setUp();
        $generatedApp = file_get_contents('tests/fixtures2/index.html');
        $this->crawler = new Crawler($generatedApp);
    }

    public function testMultilineOutputIsTextarea()
    {
        $this->assertEquals(count($this->crawler->filter('textarea[name="s0r5c1"]')), 1);
        $this->assertEquals(count($this->crawler->filter('textarea[name="s0r8c1"]')), 1);
    }
    
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        exec('rm tests/fixtures2/index.html');
    }
}
