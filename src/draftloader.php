<?php
/**
 * @package
 * @author Brian Backhaus <brianb@zoosk.com>
 */

require_once 'idraftloader.php';
require_once 'models/player.php';

class FileDraftLoader implements iDraftLoader
{
    private $projectionLocation;
    private $adpLocation;
    private $players;
    private $draftInfoLocation;

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

    private function loadFiles()
    {
        $this->loadAdpPlayers();
        $this->loadProjectedScorePlayers();
        $this->loadDraftInfo();
    }

    private function loadDraftInfo()
    {
        $draftInfoContents              = file_get_contents($this->draftInfoLocation);
        $fileLines                      = explode("\n", $draftInfoContents);

        foreach ($fileLines as $line) {
            if (preg_match('/^#/', $line) > 0) {
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

        uasort($this->players, array('Player', 'CmpPlayerADP'));
    }

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

    private function loadProjectedScorePlayers()
    {
        $projectScoreFileContents       = file_get_contents($this->projectionLocation);
        $fileLines                      = explode("\n", $projectScoreFileContents);
        $count                          = 0;

        foreach ($fileLines as $line) {
            $playerName                 = explode('"', $line);
            if (!isset($playerName[1])) {
                continue;
            }
            $playerName                 = $this->getPlayerName($playerName[1]);

            $playerInfoArr                  = explode(',', $line);
            if (!isset($playerInfoArr[19])) {
                continue;
            }

            if (!isset($this->players[$playerName])) {
                if (++$count > 1) {
//                    echo sprintf("Player name NOT SET PlayerName: %s\n", $playerName);
//                    echo sprintf("Player list: %s", print_r($this->players, true));
//                    exit;
                }

                continue;
            }

            $this->players[$playerName]->setProjectedPoints(doubleval($playerInfoArr[19]));
        }
    }

    private function getPlayerName($playerNameFuzzy)
    {
        $playerName                     = preg_replace('/\s+$/', '', $playerNameFuzzy);
        $playerName                     = preg_replace('/\s+\*$/', '', $playerName);
        return $playerName;
    }

    /**
     * @param $line
     * @return null|Player
     */
    private function getPlayerForADPLine($line)
    {
        $playerInfoArr                  = explode(',', $line);
        if (!isset($playerInfoArr[0]) || intval($playerInfoArr[0]) <= 0) {
            return null;
        }

        $playerName                     = explode('"', $line);
        if (!isset($playerName[1])) {
            return null;
        }
        $playerName                     = $this->getPlayerName($playerName[1]);

        $player                         = null;
        $playerTypeString               = $playerInfoArr[2];
        if (preg_match('/QB/', $playerTypeString) > 0) {
            $player                     = new Quarterback(
                $playerName,
                !isset($playerInfoArr[7]),
                doubleval($playerInfoArr[4]));
        } else if (preg_match('/RB/', $playerTypeString) > 0) {
            $player                     = new RunningBack(
                $playerName,
                !isset($playerInfoArr[7]),
                doubleval($playerInfoArr[4]));
        } else if (preg_match('/WR/', $playerTypeString) > 0) {
            $player                     = new WideReceiver(
                $playerName,
                !isset($playerInfoArr[7]),
                doubleval($playerInfoArr[4]));
        } else if (preg_match('/TE/', $playerTypeString) > 0) {
            $player                     = new TightEnd(
                $playerName,
                !isset($playerInfoArr[7]),
                doubleval($playerInfoArr[4]));
        }

        return $player;
    }

    public function getCurrentRound()
    {
        return $this->currentRound;
    }

    public function getDraftPosition()
    {
        return $this->draftPosition;
    }

    public function getPlayersSelectedAlready()
    {
        return $this->playersSelected;
    }

    public function getNumberTeamsInDraft()
    {
        return 12;
    }

    public function getRosterArray()
    {
        return $this->rosterArray;
    }

    public function getPlayerList()
    {
        return $this->players;
    }
}