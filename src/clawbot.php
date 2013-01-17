<?php
/**
 * @package
 * @author Brian Backhaus <brian.backhaus@gmail.com>
 */

if ($argc < 6 || $argc > 7) {
    echo sprintf("usage: php clawbot.php <projectsFileLocation> <adpFileLocation> <draftIfnoPropertiesLocation> <clawbotBaseDir> <opt_htmlDirectoryLocation>\n");
    exit;
}

/** load command line args */
$projectionsFileLoc     = $argv[1];
$adpFileLoc             = $argv[2];
$draftInfoLoc           = $argv[3];
$baseDirLocation        = $argv[4];
$webDevLoc              = isset($argv[5])?$argv[5]:null;
$fileHandler            = null;
if (!empty($webDevLoc)) {
    $fileHandler        = fopen($webDevLoc, 'w');
}

/** define constants */
define('BASE_DIR', $baseDirLocation . '/src');
define('VIEWS_DIR', $baseDirLocation . '/src/views');
define('RENDERERS_DIR', $baseDirLocation . '/src/views/renderers');
define('TEMPLATES_DIR', $baseDirLocation . '/src/views/templates');
define('MODELS_DIR', $baseDirLocation . '/src/models');

/** include for clawbot */
require_once MODELS_DIR . '/draftloader.php';
require_once BASE_DIR . '/crystalball.php';
require_once RENDERERS_DIR . '/htmlprogressrenderer.php';
require_once BASE_DIR . '/log.php';

/** instantiate and run clawbot */
$draftLoader            = new FileDraftLoader(
    $projectionsFileLoc,
    $adpFileLoc,
    $draftInfoLoc);
$htmlProgressRenderer   = new HTMLProgressRenderer($fileHandler);
$crystalBall            = new CrystalBall($draftLoader, $htmlProgressRenderer);

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