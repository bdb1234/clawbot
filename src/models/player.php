<?php
/**
 * @package
 * @author Brian Backhaus <brianb@zoosk.com>
 * @copyright Copyright (c) 2007-20011 Zoosk Inc.
 * @version $Id$
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
        return (($pickNumber - $adpLow <= $this->adp) && ($this->adp <= $pickNumber + $adpHigh)) &&
            $this->isAvailable;
    }

    public function getPlayerType()
    {
        return get_class($this);
    }
}

class Quarterback extends Player
{

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