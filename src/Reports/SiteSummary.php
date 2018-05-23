<?php

/**
 * A report listing all installed modules used in this site (from a cache).
 */
class SiteSummary extends SS_Report
{
    public function title()
    {
        return _t(__CLASS__ . '.TITLE', 'Installed modules');
    }

    public function description()
    {
        return _t(
            __CLASS__ . '.DESCRIPTION',
            <<<DESC
Provides information about what SilverStripe modules are installed,
giving an insight to project statistics such as how big the installation is,
what it would take to upgrade it, and what functionality is available
to both editors and users.
DESC
        );
    }

    public function sourceRecords($params)
    {
        $packageList = Package::get();
        $typeFilters = Config::inst()->get(UpdatePackageInfoTask::class, 'allowed_types');

        if (!empty($typeFilters)) {
            $packageList = $packageList->filter('Type', $typeFilters);
        }

        $this->extend('updateSourceRecords', $packageList);

        return $packageList;
    }

    /**
     * Provide column selection and formatting rules for the CMS report. You can extend data columns by extending
     * {@link Package::summary_fields}, or you can extend this method to adjust the formatting rules, or to provide
     * composite fields (such as Summary below) for the CMS report but not the CSV export.
     *
     * {@inheritDoc}
     */
    public function columns()
    {
        $columns = Package::create()->summaryFields();

        // Remove the default Title and Description and create Summary as a composite of both for the CMS report only
        unset($columns['Title'], $columns['Description']);
        $columns = ['Summary' => 'Summary'] + $columns;

        $this->extend('updateColumns', $columns);

        return $columns;
    }

    /**
     * Add a button row, including link out to the SilverStripe addons repository, and export button
     *
     * {@inheritdoc}
     */
    public function getReportField()
    {
        Requirements::css('silverstripe-maintenance/css/sitesummary.css');

        /** @var GridField $gridField */
        $gridField = parent::getReportField();

        $config = $gridField->getConfig();

        /** @var GridFieldExportButton $exportButton */
        $exportButton = $config->getComponentByType(GridFieldExportButton::class);
        $exportButton->setExportColumns(Package::create()->summaryFields());

        $config->addComponents(
            Injector::inst()->create('GridFieldButtonRow', 'before'),
            Injector::inst()->create('GridFieldLinkButton', 'https://addons.silverstripe.org', 'buttons-before-left')
        );

        return $gridField;
    }
}
