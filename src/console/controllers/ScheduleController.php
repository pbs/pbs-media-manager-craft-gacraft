<?php
/**
 * Media Manager
 *
 * @package       PaperTiger:MediaManager
 * @author        Paper Tiger
 * @copyright     Copyright (c) 2020 Paper Tiger
 * @link          https://www.papertiger.com/
 */

namespace papertiger\mediamanager\console\controllers;

use Craft;
use papertiger\mediamanager\jobs\ShowSync;
use yii\console\ExitCode;

use yii\console\Controller;
use papertiger\mediamanager\MediaManager;

class ScheduleController extends Controller
{
    // Protected
    // =========================================================================
    protected $allowAnonymous = [ 'run' ];


    // Public Methods
    // =========================================================================
    public function actionRun()
    {
        $scheduledSyncService = MediaManager::$plugin->scheduledSync;
        $pushableSyncs = $scheduledSyncService->getPushableSyncs();
        
        foreach ($pushableSyncs as $pushableSync) {
            Craft::$app->getQueue()->push(new ShowSync([
                'showId' => $pushableSync->showId,
                'regenerateThumbnails' => $pushableSync->regenerateThumbnail,
                'scheduledSync' => $pushableSync->id
            ]));
            
            $pushableSync->processed = 1;
            $scheduledSyncService->saveScheduledSync($pushableSync);
        }
        return ExitCode::OK;
    }
}
