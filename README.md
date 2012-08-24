# Setup and installation

First of all, make a clone or [fork of this repository](http://help.github.com/fork-a-repo/)

## Configure the .draftinfo.properties file

Here are the list of commands Clawbot supports

- DraftPosition - the position you are picking in a standard serpentine draft - `DraftPosition:12`
- Round - The current round you are in - `Round:3`
- Selected - Set this with the player names you have selected. - `Selected:Rodgers, Aaron QB GB`
- Roster - Use this to set how many of each position you would like clawbot to project for
	- Quarterback
	- RunningBack
	- TightEnd
	- WideReceiver
	`Roster:Quarterback:2
     Roster:RunningBack:2
     Roster:TightEnd:1
     Roster:WideReceiver:2`

## Configure the clawbot.sh script

Set your player stats files as well as the draft info file you want to use

## Run Clawbot!

Keep in mind, Clawbot is very dumb at the moment and brute forces his way to lineup projections.  We're looking at a runtime complexity of something like:
`O(12^N)` where N is the number of rounds out you want to project.  Probably about 7 rounds out, which takes about 4 minutes, is the most you'll want to run on
Clawbot.

