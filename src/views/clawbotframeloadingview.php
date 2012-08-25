<?php
/**
 * @package
 * @author Brian Backhaus <brianb@zoosk.com>
 * @copyright Copyright (c) 2007-20011 Zoosk Inc.
 * @version $Id$
 */

class ClawbotFrameLoading
{
    private $percentLoading;
    private $elapsedTimeString;

    public function __construct($percentLoading, $elapsedTimeString)
    {
        $this->percentLoading           = $percentLoading;
        $this->elapsedTimeString        = $elapsedTimeString;
    }

    public function render()
    {
        $percentLoading                 = $this->percentLoading;
        $elapsedTimeString              = $this->elapsedTimeString;

        ob_start();
        require 'clawbotframeloadingview_template.php';
        $output = ob_get_contents();
      	ob_end_clean();

        return $output;
    }
}