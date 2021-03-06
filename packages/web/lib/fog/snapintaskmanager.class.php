<?php
/**
 * Snapin Task Manager mass management class
 *
 * PHP version 5
 *
 * @category SnapinTaskManager
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
/**
 * Snapin Task Manager mass management class
 *
 * @category SnapinTaskManager
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
class SnapinTaskManager extends FOGManagerController
{
    /**
     * Cancels the passed tasks
     *
     * @param mixed $snapintaskids the ids to cancel
     *
     * @return void
     */
    public function cancel($snapintaskids)
    {
        /**
         * Setup our finders
         */
        $findWhere = array(
            'id' => (array)$snapintaskids
        );
        /**
         * Get our cancelled state id
         */
        $cancelled = $this->getCancelledState();
        /**
         * Get any snapin job IDs
         */
        $snapinJobIDs = self::getSubObjectIDs(
            'SnapinTask',
            $findWhere,
            'jobID'
        );
        /**
         * Update our entry to be cancelled
         */
        $this->update(
            $findWhere,
            '',
            array(
                'stateID' => $cancelled,
                'complete'=>$this->formatTime('', 'Y-m-d H:i:s')
            )
        );
        /**
         * Iterate our jobID's to find out if
         * the job needs to be cancelled or not
         */
        foreach ((array)$snapinJobIDs as $i => &$jobID) {
            /**
             * Get the snapin task count
             */
            $jobCount = self::getClass('SnapinTaskManager')
                ->count(array('jobID' => $jobID));
            /**
             * If we still have tasks start with the next job ID.
             */
            if ($jobCount > 0) {
                continue;
            }
            /**
             * If the snapin job has 0 tasks left over cancel the job
             */
            unset($snapinJobIDs[$i], $jobID);
        }
        /**
         * Only remove snapin jobs if we have any to remove
         */
        if (count($snapinJobIDs) > 0) {
            self::getClass('SnapinJobManager')
                ->cancel(array('id' => $snapinJobIDs));
        }
    }
}
