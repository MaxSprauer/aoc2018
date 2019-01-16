
// Copyright 2019 Max Sprauer
// This is Node.js code.  I'm using 10.15.0.  This was all a mistake.

const fs = require('fs');
const readline = require('readline');

function processLineByLine() {
    return new Promise(function(resolve, reject) {
        const fileStream = fs.createReadStream('input.txt');

        const rl = readline.createInterface({
            input: fileStream,
            crlfDelay: Infinity
        });

        var bots = Array();

        rl.on('line', (line) => {
            // pos=<71606898,-13016657,66984141>, r=84055915
            var regex = /pos=<(-?\d+),\s*(-?\d+),\s*(-?\d+)>,\s*r=(-?\d+)\s*/;
            var m = regex.exec(line);
    
            var bot = {
                x: parseInt(m[1]),
                y: parseInt(m[2]),
                z: parseInt(m[3]),
                r: parseInt(m[4]),
            };

            bots.push(bot);
        });

        rl.on('close', () => {
            resolve(bots);
        });
    });
}

function dist(a, b)
{
    return Math.abs(a.x - b.x) + Math.abs(a.y - b.y) + Math.abs(a.z - b.z);
}

function findLargestRadius(bots)
{
    var largest = 0;
    var bot = null;

    for (i = 0; i < bots.length; i++) {
        if (bots[i].r > largest) {
            largest = bots[i].r;
            bot = bots[i];
        }
    }

    return bot;
}

function countInRange(bots, bot)
{
    var count = 0;

    for (i = 0; i < bots.length; i++) {
        if (dist(bot, bots[i]) <= bot.r) {
            count++;
        }
    }

    return count;
}

async function main()
{
    var bots = await processLineByLine();

    var bot = findLargestRadius(bots);
    var count = countInRange(bots, bot);
    console.log(count + ' are in range.');
}

main();
