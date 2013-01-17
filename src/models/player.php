<?php
/**
 * @package
 * @author Brian Backhaus <brian.backhaus@gmail.com>
 */

require_once MODELS_DIR . '/playerenums.php';

/**
 * This is the base model class to describe a player in Clawbot.
 */
abstract class Player
{
    /**
     * @var string
     */
    private $name;

    /**
     * The projected points for the season for this player
     *
     * @var double|null
     */
    private $projectedPoints;

    /**
     * The average draft position of this player
     *
     * @var double|null
     */
    private $adp;

    /**
     * True if this player is available to be set in a lineup
     *
     * @var boolean
     */
    private $isAvailable;

    /**
     * @param string        $name
     * @param boolean       $isAvailable
     * @param double         $adp
     * @param double         $projectedPoints
     */
    public function __construct($name, $isAvailable = true, $adp = null, $projectedPoints = null)
    {
        $this->name         = $name;
        $this->isAvailable  = $isAvailable;
        $this->adp          = $adp;
        $this->projectedPoints = $projectedPoints;
    }

    /**
     * @param double         $projectedPoints
     */
    public function setProjectedPoints($projectedPoints)
    {
        $this->projectedPoints = $projectedPoints;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return double|null
     */
    public function getProjectedPoints()
    {
        return $this->projectedPoints;
    }

    /**
     * @return double|null
     */
    public function getADP()
    {
        return $this->adp;
    }

    /**
     * @param boolean       $isAvailable
     */
    public function setAvailable($isAvailable)
    {
        $this->isAvailable      = $isAvailable;
    }

    /**
     * This will return true if this player is available based on if this player's ADP is between the low and high
     * ADP as well as if this player has been picked already or not.
     *
     * @param int           $pickNumber     The current pick in the draft
     * @param int           $adpLow         The low ADP to allow
     * @param int           $adpHigh        The high ADP to allow
     * @return boolean
     */
    public function isAvailable($pickNumber, $adpLow, $adpHigh)
    {
        // is the ADP of the player between the low and the high bounds AND this player is still available
        if ((($pickNumber - $adpLow <= $this->adp) && ($this->adp <= $pickNumber + $adpHigh)) && $this->isAvailable) {
            L::Debug('Player::isAvailable', array(
                'pickNumber'    => $pickNumber,
                'adpLow'        => $adpLow,
                'adpHigh'       => $adpHigh,
                'playerName'    => $this->getName(),
                'playerADP'     => $this->getADP()));

            return true;
        }

        return false;
    }

    /**
     * @see PLAYER_TYPES
     * @return string
     */
    public function getPlayerType()
    {
        return get_class($this);
    }

    /**
     * Comparator for sort function for ADP
     *
     * @param Player $playerA
     * @param Player $playerB
     * @return int
     */
    public static function CmpPlayerADP(Player $playerA, Player $playerB)
    {
        if (doubleval($playerA->getADP()) < doubleval($playerB->getADP())) {
            return -1;
        } else if (doubleval($playerA->getADP()) > doubleval($playerB->getADP())) {
            return 1;
        }

        return 0;
    }

    /**
     * Comparator for sort function based on player projected score
     *
     * @param double        $playerScoreA
     * @param double        $playerScoreB
     * @return int
     */
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