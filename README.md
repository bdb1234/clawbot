## What is a clawbot!?

Have you ever wondered if I should take a running back in the first round, or wait and take one later?  When should I draft my QB? Clawbot will attempt to answer those questions.

Clawbot is a fantasy football simulation program.  It takes into account the players' projected scores for the season as well as the ADP(Average Draft Position) to pick lineups that are projected to score the highest.  Basically, it knows which players are likely to be available at certain rounds in the draft, and uses that information to simulate different lineups.

## Setup and installation

First of all, make a clone or [fork of this repository](http://help.github.com/fork-a-repo/)

## Configure the draftinfo.properties file

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

- `./clawbot.sh`
- Open `clawbot.thml` which will be written to your CLAWBOT_HTML directory
- During the draft
	- Use the `Selected` flag in draftinfo.properties to select players you've drafted
	- Add an `X` to the end of a player row in projections.csv
		- `"Rodgers, Aaron QB GB ",Trizzle Dizzle,528.7,355.3,4909.0,40.7,8.7,115.6,55.0,253.7,4.6,3.7,0,0,0,0.0,0,2.0,382.20` becomes `"Rodgers, Aaron QB GB ",Trizzle Dizzle,528.7,355.3,4909.0,40.7,8.7,115.6,55.0,253.7,4.6,3.7,0,0,0,0.0,0,2.0,382.20,X`
- Keep in mind, Clawbot is very dumb at the moment and brute forces his way to lineup projections.  We're looking at a runtime complexity of something like:
`O(12^N)` where N is the number of rounds out you want to project.  You'll want to keep it around 6 or 7 rounds until it can be optimized.

## TODO

- Build an actual web interface other than the rudimentary templates
- Update how we remove players from draft contention
- Add a percentage to the status page
- Update clawbot to pull in updated stats automatically
- Improve performance.
    - We're starting at the beginning of our sorted list for every pick even in the later rounds. We could save a lot of time by skipping those results.
    - We could precompute results
    - Run different processes of Clawbot in parallel.
