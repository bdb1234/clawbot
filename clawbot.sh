# setup environment

NOW=$(date -u +%Y-%m-%d)
ADPFILE=./adp.csv
PROJECTIONSFILE=./projections.csv
DRAFTINFOFILE=./draftinfo.properties
CLAWBOT_HTML=/Library/WebServer/Documents/clawbot.html

php clawbot.php $PROJECTIONSFILE $ADPFILE $DRAFTINFOFILE $CLAWBOT_HTML

