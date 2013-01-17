# Setup and installation

First of all, make a clone or [fork of this repository](http://help.github.com/fork-a-repo/)

## Configure the .draftinfo.properties file

Here are the list of commands Clawbot supports

- DraftPosition - the position you are picking in a standard serpentine draft - `DraftPosition:1`
- TeamsInDraft - the number of teams in your draft - `TeamsInDraft:12`
- Selected - Set this with the player names you have selected. MUST MATCH THE NAME OF THE PLAYER EXACTLY- `Selected:Rodgers, Aaron QB GB`
- Roster - Use this to set how many of each position you would like clawbot to project for
	- Quarterback - `Roster:Quarterback:2`
	- RunningBack - `Roster:RunningBack:2`
	- TightEnd - `Roster:TightEnd:1`
	- WideReceiver - `Roster:WideReceiver:2`

## Configure the clawbot.sh script

Set your player stats files as well as the draft info file you want to use
- ADPFILE - location of the adp scores
- PROJECTIONSFILE - location of the score projections
- DRAFTINFOFILE - location of draftinfo.properties
- BASE_DIR - the source directory where clawbot is deployed
- CLAWBOT_HTML - location where clawbot's status page will be written.

## Run Clawbot!

`./clawbot.sh`
Keep in mind, Clawbot is very dumb at the moment and brute forces his way to lineup projections.

We're looking at a runtime complexity of something like:
`O(12^N)` where N is the number of rounds out you want to project.

## TODO

- Build an actual web interface other than the rudimentary templates
- Add a percentage to the status page
- Improve performance.
    - We could precompute results
    - Run different processes of Clawbot in parallel.
