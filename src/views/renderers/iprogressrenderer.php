<?php
/**
 * @package
 * @author Brian Backhaus <brian.backhaus@gmail.com>
 */

/**
 * Implement a progress renderer and pass into our CrystalBall for updates on progress
 */
interface iProgressRenderer
{
    /**
     * @param int           $elapsedTime
     * @param int           $totalPicks         total number of picks made so far
     * @return mixed
     */
    public function renderProgress($elapsedTime, $totalPicks);
}