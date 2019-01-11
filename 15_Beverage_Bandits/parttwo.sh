#!/bin/bash

# Lazy or smart?  You be the judge.

# https://stackoverflow.com/questions/845863/how-to-use-in-an-xargs-command
echo 19 18 17 16 15 14 13 12 11 | xargs -n 1 -P 8 -I XXX sh -c 'php battle.php XXX > XXX.log' 
