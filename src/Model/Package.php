<?php

namespace BringYourOwnIdeas\Maintenance\Model;

use SilverStripe\Core\Injector\Injector;
use BringYourOwnIdeas\Maintenance\Tasks\UpdatePackageInfoTask;
use SilverStripe\ORM\DataObject;

/**
 * Describes an installed composer package version.
 */
class Package extends DataObject
{
    private static $table_name = 'Package';

    private static $db = [
        'Name' => 'Varchar(255)',
        'Description' => 'Varchar(255)',
        'Version' => 'Varchar(255)',
        'Type' => 'Varchar(255)',
        'Supported' => 'Boolean',
    ];

    private static $summary_fields = [
        'Title' => 'Title',
        'Description' => 'Description',
        'Version' => 'Version',
    ];

    /**
     * Strips vendor and 'silverstripe-' prefix from Name property
     * @return string More easily digestable module name for human consumers
     */
    public function getTitle()
    {
        return preg_replace('#^[^/]+/(silverstripe-)?#', '', $this->Name);
    }

    /**
     * Returns HTML formatted summary of this object, uses a template to do this.
     * @return string
     */
    public function getSummary()
    {
        return $this->renderWith('Package_summary');
    }

    /**
     * requireDefaultRecords() gets abused to update the information on dev/build.
     */
    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        $task = Injector::inst()->create(UpdatePackageInfoTask::class);
        $task->run(null);
    }
}
