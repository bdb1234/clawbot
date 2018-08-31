<?php
/**
 * @package
 * @author Brian Backhaus <brian.backhaus@gmail.com>
 */

require_once MODELS_DIR . '/idraftloader.php';
require_once MODELS_DIR . '/player.php';

/**
 * This class encapsulates reading the various properties and data files and building up the different model objects.
 */
class FileDraftLoader implements iDraftLoader
{
    /**
     * The location of the projected scores for players.
     *
     * @var string
     */
    private $projectionLocation;

    /**
     * The location of the ADP file for players.
     *
     * @var string
     */
    private $adpLocation;

    /**
     * This is an array indexed by player name of 'Player' objects
     *
     * @var array
     */
    private $players;

    /**
     * The location of the draftinfo.properties file
     *
     * @var string
     */
    private $draftInfoLocation;

    /**
     * An array indexed by 'PLAYER_TYPE' of integers representing how many of each position of that type of player
     * is allowed
     *
     * @var array
     */
    private $rosterArray;

    /**
     * The current round we're in
     *
     * @var int
     */
    private $currentRound;

    /**
     * Array indexed by player name of the the 'Player' model objects representing the players that have been selected
     * in this simulation.
     *
     * @var array
     */
    private $playersSelected;

    /**
     * @var int
     */
    private $numberPlayersInDraft;

    /**
     * @param string            $projectionLocation
     * @param string            $adpLocation
     * @param string            $draftInfoLocation
     */
    public function __construct($projectionLocation, $adpLocation, $draftInfoLocation)
    {
        $this->projectionLocation       = $projectionLocation;
        $this->adpLocation              = $adpLocation;
        $this->draftInfoLocation        = $draftInfoLocation;
        $this->draftPosition            = 1;
        $this->currentRound             = 1;
        $this->playersSelected          = array();
        $this->rosterArray              = array(
            PLAYER_TYPES::QUARTERBACK => 2,
            PLAYER_TYPES::RUNNING_BACK => 2,
            PLAYER_TYPES::TIGHT_END => 1,
            PLAYER_TYPES::WIDE_RECEIVER => 2
        );
        $this->players                  = array();

        $this->loadFiles();
    }

    /**
     * this function will load all the property and data files and build the models for all the players.
     */
    private function loadFiles()
    {
        $this->loadAdpPlayers();
        $this->loadProjectedScorePlayers();
        $this->loadDraftInfo();
    }

    /**
     * Load the draftinfo.properties file
     */
    private function loadDraftInfo()
    {
        $draftInfoContents              = file_get_contents($this->draftInfoLocation);
        $fileLines                      = explode("\n", $draftInfoContents);

        foreach ($fileLines as $line) {
            if (preg_match('/^#/', $line) > 0) {
                //comment, continue
                continue;
            }

            if (preg_match('/DraftPosition/', $line) > 0) {
                $draftPositionArr = explode(':', $line);
                $this->draftPosition    = intval($draftPositionArr[1]);
                echo sprintf("draftposition %s\n", $this->draftPosition);
            }

            if (preg_match('/Round/', $line) > 0) {
                $roundArr = explode(':', $line);
                $this->currentRound     = intval($roundArr[1]);
                echo sprintf("current round %s\n", $this->currentRound);
            }

            if (preg_match('/Selected/', $line) > 0) {
                $selectedArr  = explode(':', $line);
                $player     = $this->players[$selectedArr[1]];
                $this->playersSelected[] = $player;
                echo sprintf("player selected %s\n", $player->getName());
            }

            if (preg_match('/TeamsInDraft/', $line) > 0) {
                $teammsArr  = explode(':', $line);
                $this->numberPlayersInDraft = intval($teammsArr[1]);
                echo sprintf("number teams in draft %s\n", $this->numberPlayersInDraft);
            }

            if (preg_match('/Roster/', $line) > 0) {
                $rosterArr     = explode(':', $line);
                switch ($rosterArr[1]) {
                    case PLAYER_TYPES::QUARTERBACK:
                        $this->rosterArray[PLAYER_TYPES::QUARTERBACK] = intval($rosterArr[2]);
                        break;
                    case PLAYER_TYPES::RUNNING_BACK:
                        $this->rosterArray[PLAYER_TYPES::RUNNING_BACK] = intval($rosterArr[2]);
                        break;
                    case PLAYER_TYPES::TIGHT_END:
                        $this->rosterArray[PLAYER_TYPES::TIGHT_END] = intval($rosterArr[2]);
                        break;
                    case PLAYER_TYPES::WIDE_RECEIVER:
                        $this->rosterArray[PLAYER_TYPES::WIDE_RECEIVER] = intval($rosterArr[2]);
                        break;
                }
            }

            $player                     = $this->getPlayerForADPLine($line);
            if (empty($player)) {
                continue;
            }

            $this->players[$player->getName()] = $player;
        }

        //sort the players by ADP
        uasort($this->players, array('Player', 'CmpPlayerADP'));
    }

    /**
     * Create all player model objects and populate the ADP for each from the data file
     */
    private function loadAdpPlayers()
    {
        $adpFileContents                = file_get_contents($this->adpLocation);
        $fileLines                      = explode("\n", $adpFileContents);

        foreach ($fileLines as $line) {
            $player                     = $this->getPlayerForADPLine($line);
            if (empty($player)) {
                continue;
            }

            $this->players[$player->getName()] = $player;
        }
    }

    /**
     * Using the projected scores, load that data into the player model objects.
     */
    private function loadProjectedScorePlayers()
    {
        $projectScoreFileContents       = file_get_contents($this->projectionLocation);
        $fileLines                      = explode("\n", $projectScoreFileContents);
        $count                          = 0;

        foreach ($fileLines as $line) {
            $playerInfoArr              = explode(',', $line);
            if (!isset($playerInfoArr[1])) {
                continue;
            }
            $playerName                 = $this->getPlayerName($playerInfoArr[1]);

            if (!isset($playerInfoArr[19])) {
                continue;
            }

            if (!isset($this->players[$playerName])) {
                if (++$count > 1) {
                    L::Debug(sprintf("Player name NOT SET PlayerName: %s -- no ADP?", $playerName));
                }

                continue;
            }

            $this->players[$playerName]->setProjectedPoints(doubleval($playerInfoArr[19]));
        }

        foreach ($this->players as $player) {
            L::Debug(sprintf("Player Name %s Points %s ADP %s", $player->getName(), $player->getProjectedPoints(), $player->getADP()));
        }
    }

    /**
     * This function will normalize the player names into a standard format.
     *
     * @param string        $playerNameFuzzy        player name before normalization
     * @return string       player name after normalization
     */
    private function getPlayerName($playerNameFuzzy)
    {
        $playerParts                  = $playerNameFuzzy;
        $playerParts = explode('|', $playerParts);

        $playerName = trim($playerParts[0]);
        $playerNameParts = explode(' ', $playerName);

        array_pop($playerNameParts);
        $playerName = implode(' ', $playerNameParts);
        return $playerName;
    }

    /**
     * get the player type from the string line
     *
     * @param string        $playerNameString
     * @return string
     */
    private function getPlayerType($playerNameString)
    {
        $playerParts                  = $playerNameString;
        $playerParts = explode('|', $playerParts);

        $playerName = trim($playerParts[0]);

        $playerNameParts = explode(' ', $playerName);
        $playerTypeString = $playerNameParts[count($playerNameParts) - 1];
        return $playerTypeString;
    }

    /**
     * Get a player for a given line or return null
     *
     * @param string        $line                   the line in the file
     * @return null|Player
     */
    private function getPlayerForADPLine($line)
    {
        $playerInfoArr                  = explode(',', $line);
        if (!isset($playerInfoArr[0]) || intval($playerInfoArr[0]) <= 0) {
            return null;
        }

        $playerName                     = $this->getPlayerName($playerInfoArr[1]);
        $playerTypeString               = $this->getPlayerType($playerInfoArr[1]);
        $player                         = null;
        if (preg_match('/QB/', $playerTypeString) > 0) {
            $player                     = new Quarterback(
                $playerName,
                !isset($playerInfoArr[6]),
                doubleval($playerInfoArr[3]));
        } else if (preg_match('/RB/', $playerTypeString) > 0) {
            $player                     = new RunningBack(
                $playerName,
                !isset($playerInfoArr[6]),
                doubleval($playerInfoArr[3]));
        } else if (preg_match('/WR/', $playerTypeString) > 0) {
            $player                     = new WideReceiver(
                $playerName,
                !isset($playerInfoArr[6]),
                doubleval($playerInfoArr[3]));
        } else if (preg_match('/TE/', $playerTypeString) > 0) {
            $player                     = new TightEnd(
                $playerName,
                !isset($playerInfoArr[6]),
                doubleval($playerInfoArr[3]));
        }

        return $player;
    }

    /**
     * @return int
     */
    public function getDraftPosition()
    {
        return $this->draftPosition;
    }

    /**
     * @return array
     */
    public function getPlayersSelectedAlready()
    {
        return $this->playersSelected;
    }

    /**
     * @return int
     */
    public function getNumberTeamsInDraft()
    {
        return $this->numberPlayersInDraft;
    }

    /**
     * @return array
     */
    public function getRosterArray()
    {
        return $this->rosterArray;
    }

    /**
     * @return array
     */
    public function getPlayerList()
    {
        return $this->players;
    }
}