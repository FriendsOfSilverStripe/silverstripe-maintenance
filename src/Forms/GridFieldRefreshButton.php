<?php

namespace BringYourOwnIdeas\Maintenance\Forms;

use ArrayData;
use CheckForUpdatesJob;
use Convert;
use GridField;
use GridField_ActionProvider;
use GridField_FormAction;
use GridField_HTMLProvider;
use GridField_URLHandler;
use Injector;
use QueuedJob;
use QueuedJobService;
use Requirements;

/**
 * Adds a "Refresh" button to the bottom or top of a GridField.
 *
 * @package forms
 * @subpackage fields-gridfield
 */
class GridFieldRefreshButton implements GridField_HTMLProvider, GridField_ActionProvider, GridField_URLHandler
{
    /**
     * @var array
     * @config
     */
    private static $allowed_actions = ["check"];

    /**
     * Fragment to write the button to.
     * @var string
     */
    protected $targetFragment;

    /**
     * @param string $targetFragment The HTML fragment to write the button into
     */
    public function __construct($targetFragment)
    {
        $this->targetFragment = $targetFragment;
    }

    /**
     * @param GridField $gridField
     * @return array
     */
    public function getHTMLFragments($gridField)
    {
        Requirements::javascript('silverstripe-maintenance/javascript/CheckForUpdates.js');

        $button = GridField_FormAction::create(
            $gridField,
            'refresh',
            _t('GridFieldRefreshButton.REFRESH', 'Check for updates'),
            'refresh',
            null
        );

        $button->setAttribute('data-icon', 'arrow-circle-double');
        $button->setAttribute('data-check', $gridField->Link('check'));
        $button->setAttribute(
            'data-message',
            _t(
                'GridFieldRefreshButton.MESSAGE',
                'Updating this list may take 2-3 minutes. You can continue to use the CMS while we run the update.'
            )
        );

        if ($this->hasActiveJob()) {
            $button->setTitle(_t('GridFieldRefreshButton.UPDATE', 'Updating...'));
            $button->setDisabled(true);
        }

        return [
            $this->targetFragment => ArrayData::create([
                'Button' => $button->Field()
            ])->renderWith('GridFieldRefreshButton')
        ];
    }

    /**
     * Refresh is an action button.
     *
     * @param GridField $gridField
     *
     * @return array
     */
    public function getActions($gridField)
    {
        return ['refresh'];
    }

    /**
     * Handle the refresh action.
     *
     * @param GridField $gridField
     * @param string $actionName
     * @param array $arguments
     * @param array $data
     *
     * @return null
     */
    public function handleAction(GridField $gridField, $actionName, $arguments, $data)
    {
        if ($actionName == 'refresh') {
            return $this->handleRefresh($gridField);
        }
    }

    /**
     * Refresh is accessible via the url
     *
     * @param GridField $gridField
     * @return array
     */
    public function getURLHandlers($gridField)
    {
        return [
            'check' => 'handleCheck'
        ];
    }

    /**
     * @see hasActiveJob
     * @return string JSON boolean
     */
    public function handleCheck()
    {
        $isRunning = $this->hasActiveJob();
        return Convert::raw2json($isRunning);
    }

    /**
     * Check the queue for refresh jobs that are not 'done'
     * in one manner or another (e.g. stalled or cancelled)
     *
     * @return boolean
     */
    public function hasActiveJob()
    {
        $jobList = Injector::inst()
            ->get(QueuedJobService::class)
            ->getJobList(QueuedJob::QUEUED)
            ->filter([
                'Implementation' => CheckForUpdatesJob::class
            ])
            ->exclude([
                'JobStatus' => [
                    QueuedJob::STATUS_COMPLETE,
                    QueuedJob::STATUS_CANCELLED,
                    QueuedJob::STATUS_BROKEN
                ]
            ]);

        return $jobList->exists();
    }

    /**
     * Handle the refresh, for both the action button and the URL
     */
    public function handleRefresh()
    {
        if (!$this->hasActiveJob()) {
            $injector = Injector::inst();
            $injector->get(QueuedJobService::class)->queueJob($injector->create(CheckForUpdatesJob::class));
        }
    }
}
