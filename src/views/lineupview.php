<?php
/**
 * @package
 * @author Brian Backhaus <brianb@zoosk.com>
 * @copyright Copyright (c) 2007-20011 Zoosk Inc.
 * @version $Id$
 */

class LineupView
{
    private $crystalBall;
    private $projectionsToShow;

    public function __construct(CrystalBall $crystalBall, $projectionsToShow = 40)
    {
        $this->crystalBall          = $crystalBall;
        $this->projectionsToShow    = $projectionsToShow;
    }

    public function render()
    {
        $scores                     = $this->crystalBall->getScores();
        $projectionsToShow          = $this->projectionsToShow;

        ob_start();
        require 'lineupview_template.php';
        $output = ob_get_contents();
      	ob_end_clean();

        return $output;
    }
}