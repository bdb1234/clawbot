<?php
/**
 * @package
 * @author Brian Backhaus <brianb@zoosk.com>
 * @copyright Copyright (c) 2007-20011 Zoosk Inc.
 * @version $Id$
 */
 
require_once './src/draftloader.php';
require_once './src/crystalball.php';

if ($argc > 5) {
    var_dump($argv);
    echo sprintf("usage: php clawbot.php <projectsFileLocation> <adpFileLocation> <draftIfnoPropertiesLocation>\n");
    exit;
}

$projectionsFileLoc     = $argv[1];
$adpFileLoc             = $argv[2];
$draftInfoLoc           = $argv[3];
$webDevLoc              = isset($argv[4])?$argv[4]:null;
$fileHandler            = null;
if (!empty($webDevLoc)) {
    $fileHandler        = fopen($webDevLoc, 'w');
}

$draftLoader            = new FileDraftLoader(
    $projectionsFileLoc,
    $adpFileLoc,
    $draftInfoLoc);
$crystalBall            = new CrystalBall($draftLoader, $fileHandler);

$crystalBall->projectLineup();
$crystalBall->printScores();
$crystalBall->printDraftTrends();

if (!empty($webDevLoc)) {
    require_once './src/views/lineupview.php';
    require_once './src/views/drafttrendsview.php';
    require_once './src/views/clawbotframeview.php';
    $lineupView         = new LineupView($crystalBall);
    $draftTrendsView    = new DraftTrendsView($crystalBall);
    $clawbotFrame       = new ClawbotFrame(array($lineupView, $draftTrendsView));

    ftruncate($fileHandler, 0);
    fwrite($fileHandler, $clawbotFrame->render());
    fclose($fileHandler);
}