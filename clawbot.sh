# setup environment

NOW=$(date -u +%Y-%m-%d)
ADPFILE=./adp.csv
PROJECTIONSFILE=./projections.csv
DRAFTINFOFILE=./draftinfo.properties

php clawbot.php $PROJECTIONSFILE $ADPFILE $DRAFTINFOFILE

