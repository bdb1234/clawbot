<?php
/**
 * @package
 * @author Brian Backhaus <brianb@zoosk.com>
 */
 
class Roster
{
    private $positionsAvailable;
    private $rosterList;
    private $numPositionsLeft;
    private $rosterSize;

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

    public function getRosterSize()
    {
        return $this->rosterSize;
    }

    public function setPlayer(Player $player)
    {
        if ($this->isPositionAvailable($player)) {
//            echo sprintf("SELECTED A PLAYER: %s \n", $player->getName());

            $this->positionsAvailable[$player->getPlayerType()]--;
            $this->numPositionsLeft--;
            $this->rosterList[$player->getName()] = $player;
        }
    }

    public function removePlayer($playerName)
    {
        if (!isset($this->rosterList[$playerName])) {
            echo "REMOVING NOT FOUND PLAYER!";
            var_dump($playerName);
            var_dump($this->rosterList);
            exit;
            return null;
        }

        $player                         = $this->rosterList[$playerName];
        $this->positionsAvailable[$player->getPlayerType()]++;
        $this->numPositionsLeft++;


        unset($this->rosterList[$playerName]);
        return true;
    }

    public function getProjectedScore()
    {
        $totalScore                     = 0.0;
        foreach($this->rosterList as $player) {
            $totalScore += $player->getProjectedPoints();
        }

        return $totalScore;
    }

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

    public function areAnymorePositionsAvailable()
    {
//        var_dump($this->numPositionsLeft);
        return $this->numPositionsLeft > 0;
    }

    public function getRosterArrayCopy()
    {
        $rosterArrCopy      = array();
        foreach ($this->rosterList as $player) {
            $rosterArrCopy[] = $player;
        }

        return $rosterArrCopy;
    }

    public static function RosterArrayToString($rosterArr)
    {
        $buffer             = array();
        foreach ($rosterArr as $player) {
            $buffer[]       = sprintf("%s, ADP: %s \n", $player->getName(), $player->getADP());
        }

        return implode('', $buffer);
    }

    public function toString(array $rosterArr = array())
    {
        if (empty($rosterArr)) {
            $rosterArr      = $this->rosterList;
        }

        $buffer             = array();
        foreach ($rosterArr as $player) {
            $buffer[]       = sprintf("%s, ADP: %s \n", $player->getName(), $player->getADP());
        }

        return implode('', $buffer);
    }
}