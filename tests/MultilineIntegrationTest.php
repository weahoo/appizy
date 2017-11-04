<?php

use Appizy\Command\ConvertCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DomCrawler\Crawler;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MultilineIntegrationTest
 *
 * @author Usama Ahmed Khan
 */
class MultilineIntegrationTest extends PHPUnit_Framework_TestCase {
    
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        exec('bin/appizy convert tests/fixtures2/multiline-input.ods');
    }
    
    protected function setUp() {
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
