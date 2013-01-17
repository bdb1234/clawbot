<?php
/**
 * @package
 * @author Brian Backhaus <brian.backhaus@gmail.com>
 */

/**
 * This interface describes everything needed feed your own draft loader into Clawbot which
 * you would need to do if you're using a different set of data.
 */
interface iDraftLoader
{
    /**
     * @abstract
     * @return int
     */
    public function getDraftPosition();

    /**
     * @abstract
     * @return array
     */
    public function getRosterArray();

    /**
     * @abstract
     * @return array
     */
    public function getPlayersSelectedAlready();

    /**
     * @abstract
     * @return int
     */
    public function getNumberTeamsInDraft();

    /**
     * @abstract
     * @return array
     */
    public function getPlayerList();
}