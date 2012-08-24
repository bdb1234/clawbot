<?php
/**
 * @package
 * @author Brian Backhaus <brianb@zoosk.com>
 * @copyright Copyright (c) 2007-20011 Zoosk Inc.
 * @version $Id$
 */

require_once 'models/roster.php';

class CrystalBall
{
    private $draftLoader;
    private $rosterList;
    private $draftRound;
    private $currentPickNumber;
    private $draftLocation;
    private $adpLow;
    private $adpHigh;
    private $scores;
    private $totalPicks;
    private $rosterTrends;
    private $startTime;
    private $projectedCalculations;

    public function __construct(iDraftLoader $draftLoader)
    {
        $this->draftLoader          = $draftLoader;
        $this->rosterList           = array();
        $this->adpLow               = 0;
        $this->adpHigh              = 12;
        $this->scores               = array();
        $this->rosterTrends         = array();
        $this->totalPicks           = 0;
        date_default_timezone_set('America/Los_Angeles');
        $this->startTime            = mktime();
        $this->projectedCalculations = 0;
    }

    public function projectLineup()
    {
        $playerList                 = $this->draftLoader->getPlayerList();
        $playersSelectedAlready     = $this->draftLoader->getPlayersSelectedAlready();
        $this->draftLocation        = $this->draftLoader->getDraftPosition();
        $this->draftRound           = $this->draftLoader->getCurrentRound();
        $this->currentPickNumber    = $this->getInitialPickNumber($this->draftRound, $this->draftLocation);
        $roster                     = new Roster($this->draftLoader->getRosterArray());
        $rosterSize                 = $roster->getRosterSize();
        $this->projectedCalculations = pow(9, $rosterSize - 1);

        for ($i = 0; $i < $rosterSize; $i++) {
            $this->rosterTrends[$i] = array();
        }

        //add selected players
        foreach ($playersSelectedAlready as $player) {
            if (!$roster->isPositionAvailable($player)) {
                echo sprintf("POSITION FOR SELECTED PLAYER NOT AVAILABLE");
                continue;
            }

            $roster->setPlayer($player);
        }

        $this->selectPlayer($this->currentPickNumber, $this->draftRound, $roster, $playerList);
        $this->sortScores();
    }

    public function printDraftTrends()
    {
        $totalToCount               = 200;
        $count                      = 0;
        foreach ($this->scores as $projectedScore => $rosterArray) {
            $rosterIndex            = 0;
            foreach ($rosterArray as $player) {
                if (!isset($this->rosterTrends[$rosterIndex][$player->getName()])) {
                    $this->rosterTrends[$rosterIndex][$player->getName()] = 0;
                }

                $this->rosterTrends[$rosterIndex][$player->getName()] = $this->rosterTrends[$rosterIndex][$player->getName()] + 1;
                $rosterIndex++;
            }

            if (++$count >= $totalToCount) break;
        }

        foreach ($this->rosterTrends as $round => $playerNameArray) {
            echo sprintf("\nRound %s notables:\n", $round + 1);

            arsort($playerNameArray);
            foreach ($playerNameArray as $playerName => $numberOfTimesShow) {
                echo sprintf("%s, chosen %s percent of the time.\n", $playerName, strval($numberOfTimesShow / $totalToCount * 100));
            }
        }
    }

    public function printScores()
    {
        $count                      = 0;
        foreach ($this->scores as $projectedScore => $rosterArray) {
            echo sprintf(
                "Projected Score %s \n Roster: %s\n\n\n",
                $projectedScore,
                Roster::RosterArrayToString($rosterArray));

            if (++$count > 20) {
                break;
            }
        }

        echo sprintf("Scores array size %s\n", count($this->scores));
        echo sprintf("TOTAL PICKS: %s\n", $this->totalPicks);
        echo $this->getElapsedTime();
    }

    private function sortScores()
    {
//        usort($this->scores, $this->cmp_player_score);
        uksort($this->scores, array('CrystalBall', 'CmpPlayerScore'));
    }

    private static function CmpPlayerScore($playerScoreA, $playerScoreB)
    {
        if (doubleval($playerScoreA) < doubleval($playerScoreB)) {
            return 1;
        } else if (doubleval($playerScoreA) > doubleval($playerScoreB)) {
            return -1;
        }

        return 0;
    }

    private function getElapsedTime()
    {
        $endTime                    = mktime();
        return sprintf("TOTAL TIME: %s:%s", round(($endTime - $this->startTime) / 60), ($endTime - $this->startTime) % 60);
    }

    /**
     * @param $pickNumber
     * @param $draftRound
     * @param Roster $roster
     * @param $playerList
     * @return void
     */
    private function selectPlayer($pickNumber, $draftRound, $roster, $playerList)
    {
        if (!$roster->areAnymorePositionsAvailable()) {
            $this->scores[strval($roster->getProjectedScore())] = $roster->getRosterArrayCopy();
            $this->totalPicks++;
            if ($this->totalPicks % 100000 === 0) {
                echo sprintf(
                    "percent done %s, time %s\n",
                    round($this->totalPicks / $this->projectedCalculations, 2) * 100,
                    $this->getElapsedTime());
            }
            return;
        }

        foreach ($playerList as $player) {
//            echo sprintf("playerName %s, ADP:%s, pickNumber:%s \n", $player->getName(), $player->getADP(), $pickNumber);

            if ($player->getADP() > $pickNumber + $this->adpHigh) {
//                echo "highder ADP than the max \n";
                //we have a player that has a higher ADP than the max we'll go, so we can just stop here
                return;
            }

            $adpLow         = $this->adpLow;
            if ($draftRound === 1) {
                $adpLow     = 0;
            } else if ($draftRound <= 3) {
                $adpLow     = 2;
            }

            if (!$player->isAvailable($pickNumber, $adpLow, $this->adpHigh)) {
//                echo "player NOT avaialbe \n";
//                var_dump($player);
                //player is not available, keep going
                continue;
            }

            if (!$roster->isPositionAvailable($player)) {
//                echo "position NOT available \n";
                //position is not available keep going
                continue;
            }

            //set the player we've found
            $roster->setPlayer($player);

            //get the updated pick number for the next round
            $updatedPickNumber  = $this->getUpdatedPickNumber($pickNumber, $draftRound);

            //make recursive call to select another player
            $this->selectPlayer(
                $updatedPickNumber,
                $draftRound + 1,
                $roster,
                $playerList);

            //let's take out the last player and keep going
            $roster->removePlayer($player->getName());
        }
    }

    private function getInitialPickNumber($draftRound, $draftLocation)
    {

//        1  2  3  4  5  6  7  8  9  10  11  12
//        24 23 22 21 20 19 18 17 16 15  14  13
//        25 26 27 28 29 30 31 32 33 34  35  36
//
//        12 - 5 = 7 * 2 = 14
        $playersInDraft     = $this->draftLoader->getNumberTeamsInDraft();
        if ($draftRound % 2 === 0) {

            return ($draftRound * $playersInDraft) - $draftLocation + 1;
        }

        return $draftRound * $playersInDraft - ($playersInDraft - $draftLocation);
    }

    private function getUpdatedPickNumber($pickNumber, $draftRound)
    {
        $playersInDraft     = $this->draftLoader->getNumberTeamsInDraft();
        $draftLocation      = $this->draftLoader->getDraftPosition();
        if ($draftRound % 2 === 1) {
            //even round
            return $pickNumber + 1 + (($playersInDraft - $draftLocation) * 2);
        }

        return $pickNumber - 1 + ($draftLocation * 2);
    }
}