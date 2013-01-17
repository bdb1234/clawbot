<?php
/**
 * @package
 * @author Brian Backhaus <brian.backhaus@gmail.com>
 */

class Roster
{
    /**
     * An array indexed by 'PLAYER_TYPE' of integers representing how many of each position of that type of player
     * is allowed
     *
     * @var array
     */
    private $positionsAvailable;

    /**
     * This is an array indexed by player name of 'Player' objects
     *
     * @var array
     */
    private $rosterList;

    /**
     * The number of total positions left to fill the roster
     *
     * @var int
     */
    private $numPositionsLeft;

    /**
     * The total number of positions available - the number of positions already set
     *
     * @var int
     */
    private $rosterSize;

    /**
     * @param array         $positionsAvailable
     */
    public function __construct(array $positionsAvailable)
    {
        $this->positionsAvailable       = $positionsAvailable;
        $this->rosterList               = array();
        $this->numPositionsLeft         = 0;
        foreach ($this->positionsAvailable as $type => $numLeft) {
            $this->numPositionsLeft     += $numLeft;
        }
        $this->rosterSize               = $this->numPositionsLeft;
    }

    /**
     * @return int
     */
    public function getRosterSize()
    {
        return $this->rosterSize;
    }

    /**
     * Set the player in the roster ONLY if there is a position available
     *
     * @param Player        $player
     */
    public function setPlayer(Player $player)
    {
        if ($this->isPositionAvailable($player)) {
//            echo sprintf("SELECTED A PLAYER: %s \n", $player->getName());

            $this->positionsAvailable[$player->getPlayerType()]--;
            $this->numPositionsLeft--;
            $this->rosterList[$player->getName()] = $player;
        }
    }

    /**
     * @param string            $playerName
     * @return bool|null        true if we've removed the player, or null if we haven't
     */
    public function removePlayer($playerName)
    {
        if (!isset($this->rosterList[$playerName])) {
            echo "REMOVING NOT FOUND PLAYER!";
            return null;
        }

        $player                         = $this->rosterList[$playerName];
        $this->positionsAvailable[$player->getPlayerType()]++;
        $this->numPositionsLeft++;

        //unset this player from our roster list
        unset($this->rosterList[$playerName]);
        return true;
    }

    /**
     * Get the total projected score of this roster.
     *
     * @return double
     */
    public function getProjectedScore()
    {
        $totalScore                     = 0.0;
        foreach($this->rosterList as $player) {
            $totalScore += $player->getProjectedPoints();
        }

        return $totalScore;
    }

    /**
     * Given the player, see if this position is allowed in the roster.
     *
     * @param Player        $player
     * @return boolean
     */
    public function isPositionAvailable(Player $player)
    {
        if (isset($this->rosterList[$player->getName()])) {
            return false;
        }

        if (!isset($this->positionsAvailable[$player->getPlayerType()])) {
            return false;
        }

        if ($this->positionsAvailable[$player->getPlayerType()] > 0) {
            return true;
        }

        return false;
    }

    /**
     * @return bool     true if there are position to fill in this roster
     */
    public function areAnymorePositionsAvailable()
    {
        return $this->numPositionsLeft > 0;
    }

    /**
     * @return array    return a deep copy of this roster
     */
    public function getRosterArrayCopy()
    {
        $rosterArrCopy      = array();
        foreach ($this->rosterList as $player) {
            $rosterArrCopy[] = $player;
        }

        return $rosterArrCopy;
    }

    /**
     * Given a roster array, print out a readable string.
     *
     * @static
     * @param array     $rosterArr
     * @return string
     */
    public static function RosterArrayToString($rosterArr)
    {
        $buffer             = array();
        foreach ($rosterArr as $player) {
            $buffer[]       = sprintf("%s, ADP: %s\n", $player->getName(), $player->getADP());
        }

        return implode('', $buffer);
    }

    /**
     * @param array     $rosterArr
     * @return string
     */
    public function toString(array $rosterArr = array())
    {
        if (empty($rosterArr)) {
            $rosterArr      = $this->rosterList;
        }

        return Roster::RosterArrayToString($rosterArr);
    }
}