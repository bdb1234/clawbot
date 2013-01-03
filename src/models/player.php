<?php
/**
 * @package
 * @author Brian Backhaus <brianb@zoosk.com>
 */

require_once 'playerenums.php';

abstract class Player
{
    private $name;
    private $projectedPoints;
    private $adp;
    private $isAvailable;

    public function __construct($name, $isAvailable = true, $adp = null, $projectedPoints = null)
    {
        $this->name         = $name;
        $this->isAvailable  = $isAvailable;
        $this->adp          = $adp;
        $this->projectedPoints = $projectedPoints;
    }

    public function setProjectedPoints($projectedPoints)
    {
        $this->projectedPoints = $projectedPoints;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getProjectedPoints()
    {
        return $this->projectedPoints;
    }

    public function getADP()
    {
        return $this->adp;
    }

    public function setAvailable($isAvailable)
    {
        $this->isAvailable      = $isAvailable;
    }

    public function isAvailable($pickNumber, $adpLow, $adpHigh)
    {
        // is the ADP of the player between the low and the high bounds AND this player is still available
        return (($pickNumber - $adpLow <= $this->adp) && ($this->adp <= $pickNumber + $adpHigh)) &&
            $this->isAvailable;
    }

    public function getPlayerType()
    {
        return get_class($this);
    }

    public static function CmpPlayerADP(Player $playerA, Player $playerB)
    {
        if (doubleval($playerA->getADP()) < doubleval($playerB->getADP())) {
            return -1;
        } else if (doubleval($playerA->getADP()) > doubleval($playerB->getADP())) {
            return 1;
        }

        return 0;
    }

    public static function CmpPlayerScore($playerScoreA, $playerScoreB)
    {
        if (doubleval($playerScoreA) < doubleval($playerScoreB)) {
            return 1;
        } else if (doubleval($playerScoreA) > doubleval($playerScoreB)) {
            return -1;
        }

        return 0;
    }
}

class Quarterback extends Player
{
    public function __construct($name, $isAvailable = true, $adp = null, $projectedPoints = null)
    {
        if (!is_null($adp)) {
            // In our league, quarterbacks on average go half as high as the ADP provided says.
            $adp        = $adp / 2;
        }
        parent::__construct($name, $isAvailable, $adp, $projectedPoints);
    }
}

class TightEnd extends Player
{

}

class RunningBack extends Player
{

}

class WideReceiver extends Player
{

}