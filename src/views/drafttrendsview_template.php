<?php
/**
 * @package
 * @author Brian Backhaus <brianb@zoosk.com>
 * @copyright Copyright (c) 2007-20011 Zoosk Inc.
 * @version $Id$
 */

?>
<div class="draft-trends-view">
    <ol>
    <?php foreach ($rosterTrends as $round => $playerNameArr) { ?>
        <li>
            <h4>Round <?=$round + 1?></h4>
            <ul>
            <?php foreach ($playerNameArr as $playerName => $numberOfTimesShown) {
                $percentShown   = $numberOfTimesShown / $topRosterTrendsCount * 100;
                $extraClass     = '';
                if ($percentShown === 100) {
                    $extraClass = 'picked';
                } else if ($percentShown > 90) {
                    $extraClass = 'great-pick';
                } else if ($percentShown > 75) {
                    $extraClass = 'good-pick';
                } else if ($percentShown > 50) {
                    $extraClass = 'decent-pick';
                }
            ?>
                <li class="draft-trend-item <?=$extraClass?>">
                    <?=sprintf(
                        "%s, chosen %s percent of the time.\n",
                        $playerName,
                        strval($percentShown))?>
                </li>
            <?php } ?>
            </ul>
        </li>
    <?php } ?>
    </ol>
</div>