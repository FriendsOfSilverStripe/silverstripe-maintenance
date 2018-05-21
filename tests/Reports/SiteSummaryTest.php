<?php

namespace BringYourOwnIdeas\Maintenance\Tests\Reports;

use Package;
use SapphireTest;
use SiteSummary;

class SiteSummaryTest extends SapphireTest
{
    protected static $fixture_file = 'Package.yml';

    public function testSourceRecords()
    {
        $summaryReport = new SiteSummary;
        $records = $summaryReport->sourceRecords(null);
        $firstRecord = $records->first();
        $this->assertInstanceOf(Package::class, $firstRecord);
        $this->assertStringStartsWith('pretend/', $firstRecord->Name);
    }

    public function testOnlySilverStripeModulesAreShown()
    {
        $summaryReport = new SiteSummary;
        $records = $summaryReport->sourceRecords(null);
        $this->assertCount(3, $records);
        foreach ($records as $record) {
            $this->assertEquals('silverstripe-module', $record->Type);
        }
    }
}
