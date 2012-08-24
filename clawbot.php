<?php
/**
 * @package
 * @author Brian Backhaus <brianb@zoosk.com>
 * @copyright Copyright (c) 2007-20011 Zoosk Inc.
 * @version $Id$
 */
 
require_once './src/draftloader.php';
require_once './src/crystalball.php';

if ($argc !== 4) {
    var_dump($argv);
    echo sprintf("usage: php clawbot.php <projectsFileLocation> <adpFileLocation> <draftIfnoPropertiesLocation>\n");
    exit;
}

$projectionsFileLoc     = $argv[1];
$adpFileLoc             = $argv[2];
$draftInfoLoc           = $argv[3];

$draftLoader            = new FileDraftLoader(
    $projectionsFileLoc,
    $adpFileLoc,
    $draftInfoLoc);
$crystalBall            = new CrystalBall($draftLoader);

$crystalBall->projectLineup();
$crystalBall->printScores();
$crystalBall->printDraftTrends();