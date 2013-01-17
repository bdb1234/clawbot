<?php
/**
 * @package
 * @author Brian Backhaus <brian.backhaus@gmail.com>
 * @copyright Copyright (c) 2007-20011 Zoosk Inc.
 * @version $Id$
 */

class DraftTrendsView
{
    /**
     * @var CrystalBall
     */
    private $crystalBall;

    public function __construct(CrystalBall $crystalBall)
    {
        $this->crystalBall          = $crystalBall;
    }

    public function render()
    {
        $rosterTrends               = $this->crystalBall->getRosterTrends();
        $topRosterTrendsCount       = $this->crystalBall->getTopRosterTrendsToUseCount();

        ob_start();
        require TEMPLATES_DIR . '/drafttrendsview_template.php';
        $output = ob_get_contents();
      	ob_end_clean();

        return $output;
    }
}