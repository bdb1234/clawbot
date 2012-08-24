<?php
/**
 * @package
 * @author Brian Backhaus <brianb@zoosk.com>
 * @copyright Copyright (c) 2007-20011 Zoosk Inc.
 * @version $Id$
 */
 
interface iDraftLoader
{
    /**
     * @abstract
     * @return number
     */
    public function getCurrentRound();

    /**
     * @abstract
     * @return number
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
     * @return number
     */
    public function getNumberTeamsInDraft();

    /**
     * @abstract
     * @return array
     */
    public function getPlayerList();
}