# setup environment

#file locations
ADPFILE=./adp.csv
PROJECTIONSFILE=./projections.csv
DRAFTINFOFILE=./draftinfo.properties

# absolute path to clawbots base directory
BASE_DIR=~/GoogleDrive/Dev/gitWorkspace/clawbot

#location of where the clawbot html will live
CLAWBOT_HTML=/Library/WebServer/Documents/clawbot.html

php src/clawbot.php $PROJECTIONSFILE $ADPFILE $DRAFTINFOFILE $BASE_DIR $CLAWBOT_HTML

