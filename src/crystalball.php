<?php
/**
 * @package
 * @author Brian Backhaus <brian.backhaus@gmail.com>
 */

require_once MODELS_DIR . '/roster.php';

/**
 * The CrystalBall class encapsulates the bulk of the logic that will simulate the different
 * rounds of the draft to predict the best lineup possible.
 */
class CrystalBall
{
    /**
     * @var iDraftLoader
     */
    private $draftLoader;

    /**
     * This is an array indexed by player name of 'Player' objects
     *
     * @var array
     */
    private $rosterList;

    /**
     * @var int
     */
    private $draftRound;

    /**
     * The current pick we're on based on a serpentine draft style.
     *
     * @var int
     */
    private $currentPickNumber;

    /**
     * @var int
     */
    private $draftLocation;

    /**
     * The low ADP deviation to allow.
     *
     * i.e. 3 would mean, we'll consider players from my pick location
     * up to 3 spots before my pick.
     *
     * @var int
     */
    private $adpLow;

    /**
     * The low ADP deviation to allow.
     *
     * i.e. 10 would mean, we'll consider players from my pick location
     * up to 10 spots after my pick.
     *
     * @var int
     */
    private $adpHigh;

    /**
     * An array indexed by the 'projected score string' of the roster array that the score index
     * represents.
     *
     * @var array
     */
    private $scores;

    /**
     * The total number of lineup simulations that clawbot performed
     *
     * @var int
     */
    private $totalPicks;

    /**
     * This is a matrix of the number of times a player was selected by round.
     *
     * i.e.
     * $rosterTrends[roundNumber][playerName] = totalNumber of times selected
     *
     * @var array
     */
    private $rosterTrends;

    /**
     * @var int
     */
    private $startTime;

    /**
     * How many of the top rosters to use to calculate trends
     *
     * @var int
     */
    private $topRosterTrendsToUseCount;

    /**
     * @var iProgressRenderer
     */
    private $progressRenderer;

    /**
     * @param iDraftLoader          $draftLoader
     * @param iProgressRenderer     $progressRenderer
     */
    public function __construct(iDraftLoader $draftLoader, iProgressRenderer $progressRenderer)
    {
        date_default_timezone_set('America/Los_Angeles');

        $this->draftLoader          = $draftLoader;
        $this->rosterList           = array();
        $this->adpLow               = 5;
        $this->adpHigh              = 12;
        $this->scores               = array();
        $this->rosterTrends         = array();
        $this->totalPicks           = 0;
        $this->startTime            = time();
        $this->topRosterTrendsToUseCount = 200;
        $this->progressRenderer     = $progressRenderer;
    }

    /**
     * This is the main function that will do the work of simulating all the different lineup selections
     * and storing the data for the top N roster scores
     */
    public function projectLineup()
    {
        $playerList                 = $this->draftLoader->getPlayerList();
        $playersSelectedAlready     = $this->draftLoader->getPlayersSelectedAlready();
        $this->draftLocation        = $this->draftLoader->getDraftPosition();
        $roster                     = new Roster($this->draftLoader->getRosterArray());
        $rosterSize                 = $roster->getRosterSize();
        $this->draftRound           = count($playersSelectedAlready) + 1;
        $this->currentPickNumber    = $this->getInitialPickNumber($this->draftRound, $this->draftLocation);
        $this->writeWebLoadingStatus();

        L::Debug('initialization', array(
            'currentPickNumber'     => $this->currentPickNumber,
            'rosterSize'            => $rosterSize,
        ));

        echo sprintf("Current Round %s\n", $this->draftRound);

        //initialize the roster triends array for each round
        for ($i = 0; $i < $rosterSize; $i++) {
            $this->rosterTrends[$i] = array();
        }

        //add selected players
        foreach ($playersSelectedAlready as $player) {
            if (!$roster->isPositionAvailable($player)) {
                L::Error("POSITION FOR SELECTED PLAYER NOT AVAILABLE");
                continue;
            }

            $roster->setPlayer($player);
        }

        //select a player for this round
        $this->selectPlayer($this->currentPickNumber, $this->draftRound, $roster, $playerList);

        //sort the scores
        $this->sortScores();

        //calculate the trends we've found
        $this->calculateRosterTrends();
    }

    /**
     * go through all the top scores and calculate the trends we've found.
     *
     * i.e. how many times a certain player is selected in each round and etc.
     */
    private function calculateRosterTrends()
    {
        $count                      = 0;
        foreach ($this->scores as $projectedScore => $rosterArray) {
            $rosterIndex            = 0;
            foreach ($rosterArray as $player) {
                if (!isset($this->rosterTrends[$rosterIndex][$player->getName()])) {
                    $this->rosterTrends[$rosterIndex][$player->getName()] = 0;
                }

                //increment a count for this player name in this round
                $this->rosterTrends[$rosterIndex][$player->getName()] = $this->rosterTrends[$rosterIndex][$player->getName()] + 1;
                $rosterIndex++;
            }

            //we've hit how many top rosters we're going to look at, so let's break here
            if (++$count >= $this->topRosterTrendsToUseCount) break;
        }
    }

    /**
     * write any web loading statuses we need to.
     */
    private function writeWebLoadingStatus()
    {
        $this->progressRenderer->renderProgress($this->getElapsedTime(), $this->totalPicks);
    }

    /**
     * Print out the draft trends to the command line
     */
    public function printDraftTrends()
    {
        foreach ($this->rosterTrends as $round => $playerNameArray) {
            echo sprintf("\nRound %s notables:\n", $round + 1);

            arsort($playerNameArray);
            foreach ($playerNameArray as $playerName => $numberOfTimesShow) {
                echo sprintf(
                    "%s, chosen %s percent of the time.\n",
                    $playerName,
                    strval($numberOfTimesShow / $this->topRosterTrendsToUseCount * 100));
            }
        }
    }

    /**
     * Print all the scores we've found
     */
    public function printScores()
    {
        $count                      = 0;
        foreach ($this->scores as $projectedScore => $rosterArray) {
            echo sprintf(
                "Projected Score %s \nRoster: %s\n\n\n",
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

    /**
     * @return int
     */
    public function getTopRosterTrendsToUseCount()
    {
        return $this->topRosterTrendsToUseCount;
    }

    /**
     * @return array
     */
    public function getScores()
    {
        return $this->scores;
    }

    /**
     * @return array
     */
    public function getRosterTrends()
    {
        return $this->rosterTrends;
    }

    /**
     * Sort the scores by the highest player score
     */
    private function sortScores()
    {
        uksort($this->scores, array('Player', 'CmpPlayerScore'));
    }

    /**
     * Elapsed time in friendly format
     *
     * @return string
     */
    private function getElapsedTime()
    {
        $endTime                    = time();
        return sprintf("TOTAL TIME: %s", date("i:s", $endTime - $this->startTime));
    }

    /**
     * This function will recursively select players until the roster has been completely filled and
     * then will return
     *
     * @param int           $pickNumber
     * @param int           $draftRound
     * @param Roster        $roster
     * @param array         $playerList
     */
    private function selectPlayer($pickNumber, $draftRound, $roster, $playerList)
    {
        if (!$roster->areAnymorePositionsAvailable()) {
            //base case, no more roster spots available!

            $this->scores[strval($roster->getProjectedScore())] = $roster->getRosterArrayCopy();
            $this->totalPicks++;
            if ($this->totalPicks % 2500000 === 0) {
                //every so often, print out a status to the command line
                $this->writeWebLoadingStatus();
                echo $this->getElapsedTime() . "\n";
            }

            return;
        }

        // inductive step.  Let's go through our player list and find a player to select
        foreach ($playerList as $player) {
//            echo sprintf("playerName %s, ADP:%s, pickNumber:%s \n", $player->getName(), $player->getADP(), $pickNumber);

            if ($player->getADP() > $pickNumber + $this->adpHigh) {
                //we have a player that has a higher ADP than the max we'll go, so we can just stop here
                return;
            }

            $adpLow         = $this->adpLow;
            if ($draftRound === 1) {
                //players usually follow adp, so don't reach that far in round 1
                $adpLow     = 0;
            } else if ($draftRound <= 3) {
                //only reach by up to 3 if we're in rounds 2 or 3
                $adpLow     = 3;
            }

            if (!$player->isAvailable($pickNumber, $adpLow, $this->adpHigh)) {
                //player is not available, keep going
                continue;
            }

            if (!$roster->isPositionAvailable($player)) {
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

    /**
     * Given the round and the draft number, give me the initial pick
     *
     * i.e. 12 person serpentine drafts look something like this
     *
     *      1  2  3  4  5  6  7  8  9  10  11  12
     *      24 23 22 21 20 19 18 17 16 15  14  13
     *      25 26 27 28 29 30 31 32 33 34  35  36
     *
     *       12 - 5 = 7 * 2 = 14
     *
     * @param int           $draftRound
     * @param int           $draftLocation
     * @return int
     */
    private function getInitialPickNumber($draftRound, $draftLocation)
    {
        $playersInDraft     = $this->draftLoader->getNumberTeamsInDraft();
        if ($draftRound % 2 === 0) {
            //if an even round
            return ($draftRound * $playersInDraft) - $draftLocation + 1;
        }

        //if an odd round
        return $draftRound * $playersInDraft - ($playersInDraft - $draftLocation);
    }

    /**
     * Given the pick number and the draft round, give me the next pick location
     *
     * @param int           $pickNumber
     * @param int           $draftRound
     * @return int
     */
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