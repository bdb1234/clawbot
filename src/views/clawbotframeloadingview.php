<?php
/**
 * @package
 * @author Brian Backhaus <brian.backhaus@gmail.com>
 * @copyright Copyright (c) 2007-20011 Zoosk Inc.
 * @version $Id$
 */

class ClawbotFrameLoading
{
    private $elapsedTimeString;

    public function __construct($elapsedTimeString)
    {
        $this->elapsedTimeString        = $elapsedTimeString;
    }

    public function render()
    {
        $elapsedTimeString              = $this->elapsedTimeString;

        ob_start();
        require TEMPLATES_DIR . '/clawbotframeloadingview_template.php';
        $output = ob_get_contents();
      	ob_end_clean();

        return $output;
    }
}