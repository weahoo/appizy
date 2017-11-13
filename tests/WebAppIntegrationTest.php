<?php

namespace Appizy;

use Appizy\Command\ConvertCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DomCrawler\Crawler;

class WebAppIntegrationTest extends TestCase
{
    /** @var  Crawler */
    protected $crawler;
    /** @var  String */
    protected $generatedApp;
    /** @var  String */
    protected $generatedScript;

    public static function setUpBeforeClass()
    {
        define('APPIZY_BASE_DIR', __DIR__ . '/..');

        $application = new Application();
        $application->add(new ConvertCommand());

        $command = $application->find('convert');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            'source' => 'tests/fixtures/demo-appizy.ods'
        ));
    }

    protected function setUp()
    {
        $this->generatedApp = file_get_contents('tests/fixtures/index.html');
        $this->crawler = new Crawler($this->generatedApp);
    }

    public function testBasicDOMComponents()
    {
        $this->assertEquals(count($this->crawler->filter('body')), 1);
        $this->assertEquals(count($this->crawler->filter('#appizy')), 1);
    }

    public function testRowSpan()
    {
        $this->assertEquals($this->crawler->filter('.s1r1c0')->attr('rowspan'), 2);
        $this->assertEquals($this->crawler->filter('.s1r1c1')->attr('rowspan'), null);
        $this->assertEquals($this->crawler->filter('.s1r1c3')->attr('rowspan'), 2);
    }

    public function testColSpan()
    {
        $this->assertEquals($this->crawler->filter('.s1r1c0')->attr('colspan'), null);
        $this->assertEquals($this->crawler->filter('.s1r1c1')->attr('colspan'), 2);
        $this->assertEquals($this->crawler->filter('.s1r1c3')->attr('colspan'), 2);
    }

    public function testDataFormatPresence()
    {
        $this->assertEquals('0%', $this->crawler->filter('input[name="s0r19c3"]')->attr('data-format'));
        $this->assertEquals('0.0%', $this->crawler->filter('input[name="s0r20c3"]')->attr('data-format'));
        $this->assertEquals('0.000%', $this->crawler->filter('input[name="s0r21c3"]')->attr('data-format'));
    }

    public function testHiddenRowShouldHaveCSSClass()
    {
        $this->assertContains(
            'hidden-row',
            $this->crawler->filter('.s1r4')->attr('class')
        );
    }

    public function testValidationListAsSelectTag()
    {
        $this->assertEquals($this->crawler->filter('#s0r4c1')->nodeName(), 'select');
    }

    public function testAbsenceOfLocalhostCallInGeneratedApplication()
    {
        preg_match_all('|\/\/localhost|', $this->generatedApp, $out);
        $this->assertCount(0, $out[0]);
    }

    public function testAbsenceOfHttp()
    {
        preg_match_all('|http:|', $this->generatedApp, $out);
        $this->assertCount(0, $out[0]);
    }

    public function testJsStatPresence()
    {
        preg_match_all('|jstat\.min\.js|', $this->generatedApp, $out);
        $this->assertCount(1, $out[0]);
    }

    public function testFormulaUniqueness()
    {
        preg_match_all('|Formula.VLOOKUP = function|', $this->generatedApp, $out);
        $this->assertCount(1, $out[0]);
    }

    public function testFormulaDependenciesPresence()
    {
        preg_match_all('|Formula.ARGSTOARRAY = function|', $this->generatedApp, $out);
        $this->assertCount(1, $out[0]);
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        exec('rm tests/fixtures/*.html');
    }
}
