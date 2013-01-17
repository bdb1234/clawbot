<?php
/**
 * @package
 * @author Brian Backhaus <brian.backhaus@gmail.com>
 */

require_once RENDERERS_DIR . '/iprogressrenderer.php';
require_once VIEWS_DIR . '/clawbotframeloadingview.php';

class HTMLProgressRenderer implements iProgressRenderer
{
    /**
     * The file handler of the file we're going to write out our HTML to
     *
     * @var resource
     */
    private $webFileHandler;

    /**
     * @param resource      $webFileHanlder
     */
    public function __construct($webFileHanlder)
    {
        $this->webFileHandler = $webFileHanlder;
    }

    /**
     * @param int           $elapsedTime
     * @param int           $totalPicks         total number of picks made so far
     * @return mixed
     */
    public function renderProgress($elapsedTime, $totalPicks)
    {
        $clawbotLoadingFrame    = new ClawbotFrameLoading($elapsedTime);
        ftruncate($this->webFileHandler, 0);
        fwrite($this->webFileHandler, $clawbotLoadingFrame->render());
    }
}