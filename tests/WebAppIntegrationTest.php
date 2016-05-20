<?php

use Appizy\Core\Command\ConvertCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DomCrawler\Crawler;

class WebAppIntegrationTest extends PHPUnit_Framework_TestCase
{
    /** @var  Crawler */
    protected $crawler;

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
        $generatedHtml = file_get_contents('tests/fixtures/app.html');
        $this->crawler = new Crawler($generatedHtml);
    }

    public function testBasicDOMComponents()
    {
        $this->assertEquals(count($this->crawler->filter('body')), 1);
        $this->assertEquals(count($this->crawler->filter('#appizy')), 1);
    }

    public function testRowSpan()
    {
        $this->assertEquals($this->crawler->filter('.s2r0c0')->attr('rowspan'), 2);
        $this->assertEquals($this->crawler->filter('.s2r0c1')->attr('rowspan'), null);
        $this->assertEquals($this->crawler->filter('.s2r0c3')->attr('rowspan'), 2);
    }

    public function testColSpan()
    {
        $this->assertEquals($this->crawler->filter('.s2r0c0')->attr('colspan'), null);
        $this->assertEquals($this->crawler->filter('.s2r0c1')->attr('colspan'), 2);
        $this->assertEquals($this->crawler->filter('.s2r0c3')->attr('colspan'), 2);
    }

    public function testHiddenRowShouldHaveCSSClass()
    {
        $this->assertContains('hidden-row',
            $this->crawler->filter('.s2r3')->attr('class'));
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        exec('rm tests/fixtures/*.html');
        exec('rm tests/fixtures/*.js');
    }
}
