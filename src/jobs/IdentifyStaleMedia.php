<?php
/**
 * Media Manager
 *
 * @package       PaperTiger:MediaManager
 * @author        Paper Tiger
 * @copyright     Copyright (c) 2020 Paper Tiger
 * @link          https://www.papertiger.com/
 */

namespace papertiger\mediamanager\jobs;

use Craft;
use craft\queue\BaseJob;
use craft\elements\Entry;

class IdentifyStaleMedia extends BaseJob
{

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $date;

    /**
     * @var string
     */
    public $tags;

    /**
     * @var int
     */
    public $sectionId;

    /**
     * @var int|array
     */
    public $siteId;

    // Public Methods
    // =========================================================================

    public function execute( $queue ): void
    {

        if (!$this->tags) {
            // too generic, exit
            return;
        }


        $relatedMediaObjects = Entry::find()->sectionId($this->sectionId)->lastSynced("< {$this->date}")->relatedTo(['and', $this->tags])->markedForDeletion(0)->siteId($this->siteId)->ids();

        foreach($relatedMediaObjects as $media) {
					if (!$this->_queueJobExists($media)) {
						$queue = Craft::$app->getQueue();
						$queue->push(new MarkStaleMedia(['entryId' => $media]));
					}
        }
    }

    // Protected Methods
    // =========================================================================

    protected function defaultDescription(): string
    {
        return Craft::t( 'mediamanager', "Marking entries for deletion." );
    }


    // Private Methods
    // =========================================================================
		private function _queueJobExists(int $entryId): bool
		{
			// Preflight check to ensure regular queue in place
			if(!Craft::$app->queue->hasProperty('jobInfo')){
				return false;
			}

			return in_array("Marking entry {$entryId} for deletion.", array_column(Craft::$app->queue->jobInfo, 'description'), true);
		}
}
