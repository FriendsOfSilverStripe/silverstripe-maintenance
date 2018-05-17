<?php

class SiteSummaryTest extends SapphireTest
{
    protected static $fixture_file = 'Package.yml';

    public function testSourceRecords()
    {
        $summaryReport = new SiteSummary;
        $records = $summaryReport->sourceRecords(null);
        $firstRecord = $records->first();
        $this->assertInstanceOf(Package::class, $firstRecord);
        $this->assertEquals('pretend/uptodate', $firstRecord->Name);
    }
}
