<?php
/**
 * @package
 * @author Brian Backhaus <brianb@zoosk.com>
 * @copyright Copyright (c) 2007-20011 Zoosk Inc.
 * @version $Id$
 */

$count      = 0;

?>
<div class="lineup-view">
    <ol>
    <?php foreach ($scores as $projectedScore => $rosterArray) {
        if ($count++ >= $projectionsToShow) break;
    ?>
        <li>
            <h4>Projected Scores: <?=$projectedScore?></h4>
            <ul>
            <?php foreach ($rosterArray as $player) { ?>
                <li>
                    <?=$player->getName()?>, ADP: <?=$player->getADP()?>
                </li>
            <?php } ?>
            </ul>
        </li>
    <?php } ?>
    </ol>
</div>