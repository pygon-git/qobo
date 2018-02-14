<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ScheduledJobsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ScheduledJobsTable Test Case
 */
class ScheduledJobsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\ScheduledJobsTable
     */
    public $ScheduledJobsTable;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.scheduled_jobs'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('ScheduledJobs') ? [] : ['className' => ScheduledJobsTable::class];
        $this->ScheduledJobsTable = TableRegistry::get('ScheduledJobs', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->ScheduledJobsTable);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getActiveJobs method
     *
     * @return void
     */
    public function testGetActiveJobs()
    {
        $result = $this->ScheduledJobsTable->getActiveJobs();

        $this->assertNotEmpty($result);
        $this->assertInstanceOf('\Cake\ORM\ResultSet', $result);
    }

    public function testGetInstance()
    {
        $result = $this->ScheduledJobsTable->getInstance();
        $this->assertNull($result);

        $result = $this->ScheduledJobsTable->getInstance('CakeShell::App:clean_modules_data', 'Handler');

        $this->assertInstanceOf('\App\ScheduledJobs\Handlers\CakeShellHandler', $result);
    }

    public function testGetList()
    {
        $result = $this->ScheduledJobsTable->getList();

        $this->assertTrue(is_array($result));
        $this->assertNotEmpty($result);
    }

    /**
     * @dataProvider providerTestIsValidFile
     */
    public function testIsValidFile($file, $expected)
    {
        $result = $this->ScheduledJobsTable->isValidFile($file);

        $this->assertEquals($result, $expected);
    }

    public function providerTestIsValidFile()
    {
        return [
            ['foobar.php', true],
            ['foo.bar', false],
        ];
    }

    public function testTimeToInvoke()
    {
        $time = new \Cake\I18n\Time('2018-01-18 09:00:00', 'UTC');

        // in 1st scenario, the rule will be executed at the beginning of each hour.
        // Due to that dtstart won't fall in condition.
        $dtstart = new \DateTime('2018-01-18 08:10:00', new \DateTimeZone('UTC'));
        $dtstartString = $dtstart->format('Y-m-d H:i:s');
        $rrule = new \RRule\RRule(['FREQ' => 'HOURLY', 'DTSTART' => $dtstartString]);

        $result = $this->ScheduledJobsTable->timeToInvoke($time, $rrule);
        $this->assertFalse($result);

        // in 2nd scenario, the rrule will be executed every minute, as
        // previous time/date vars match FREQ condition.
        $rrule2 = new \RRule\RRule(['FREQ' => 'MINUTELY', 'DTSTART' => $dtstartString]);
        $result = $this->ScheduledJobsTable->timeToInvoke($time, $rrule2);
        $this->assertTrue($result);

        unset($rrule2);
        unset($rrule);
    }

    /**
     * @dataProvider providerGetRRule
     */
    public function testGetRRule($id, $expected)
    {
        $entity = $this->ScheduledJobsTable->get($id);

        $result = $this->ScheduledJobsTable->getRRule($entity);

        if (is_null($expected)) {
            $this->assertEquals($result, $expected);
        } else {
            $this->assertInstanceOf($expected, $result);
        }
    }

    public function providerGetRRule()
    {
        return [
            ['00000000-0000-0000-0000-000000000001', '\RRule\RRule'],
            ['00000000-0000-0000-0000-000000000002', '\RRule\RRule'],
            ['00000000-0000-0000-0000-000000000003', null],
        ];
    }
}
