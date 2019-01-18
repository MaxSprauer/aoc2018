
// Copyright 2019 Max Sprauer
// This is Node.js code.  I'm using 10.15.0.  This was all a mistake.

const fs = require('fs');
const readline = require('readline');

// https://stackoverflow.com/questions/38942354/how-to-return-values-from-an-event-handler-in-a-promise
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
        }).on('close', () => {
            resolve(bots);
        });

        // I would like to catch file errors here and call reject(), but try/catch doesn't work and there's no "on error" event.
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

    for (var i = 0; i < bots.length; i++) {
        if (dist(bot, bots[i]) <= bot.r) {
            count++;
        }
    }

    return count;
}

function getEnclosingCube(bots)
{
    var cube = new Cube();
    
    for (var j = 0; j < bots.length; j++) {
        if (bots[j].x < cube.xMin) {
            cube.xMin = bots[j].x;
        }
        if (bots[j].x > cube.xMax) {
            cube.xMax = bots[j].x;
        }

        if (bots[j].y < cube.yMin) {
            cube.yMin = bots[j].y;
        }
        if (bots[j].y > cube.yMax) {
            cube.yMax = bots[j].y;
        }

        if (bots[j].z < cube.zMin) {
            cube.zMin = bots[j].z;
        }
        if (bots[j].z > cube.zMax) {
            cube.zMax = bots[j].z;
        }
    }
    
    return cube;
}

function Cube(xMin, xMax, yMin, yMax, zMin, zMax)
{
    if (xMin === undefined) {
        this.xMin = Infinity;
        this.xMax = -Infinity;
        this.yMin = Infinity;
        this.yMax = -Infinity;
        this.zMin = Infinity;
        this.zMax = -Infinity;
    } else {
        this.xMin = xMin;
        this.xMax = xMax;
        this.yMin = yMin;
        this.yMax = yMax;
        this.zMin = zMin;
        this.zMax = zMax;
    }

    this.botsInRange = 0;

    this.cutInEight = function () {
        var cubes = Array();
        var halfX = Math.floor((this.xMax - this.xMin) / 2);
        var halfY = Math.floor((this.yMax - this.yMin) / 2);
        var halfZ = Math.floor((this.zMax - this.zMin) / 2);

        cubes.push(new Cube(this.xMin, this.xMin + halfX, this.yMin, this.yMin + halfY, this.zMin, this.zMin + halfZ));
        cubes.push(new Cube(this.xMin, this.xMin + halfX, this.yMin, this.yMin + halfY, this.zMin + halfZ, this.zMax));
        cubes.push(new Cube(this.xMin, this.xMin + halfX, this.yMin + halfY, this.yMax, this.zMin, this.zMin + halfZ));
        cubes.push(new Cube(this.xMin, this.xMin + halfX, this.yMin + halfY, this.yMax, this.zMin + halfZ, this.zMax));
        cubes.push(new Cube(this.xMin + halfX, this.xMax, this.yMin, this.yMin + halfY, this.zMin, this.zMin + halfZ));
        cubes.push(new Cube(this.xMin + halfX, this.xMax, this.yMin, this.yMin + halfY, this.zMin + halfZ, this.zMax));
        cubes.push(new Cube(this.xMin + halfX, this.xMax, this.yMin + halfY, this.yMax, this.zMin, this.zMin + halfZ));
        cubes.push(new Cube(this.xMin + halfX, this.xMax, this.yMin + halfY, this.yMax, this.zMin + halfZ, this.zMax));

        return cubes;
    }

    this.countBotsInRange = function (bots) {
        this.botsInRange = 0;

        for (var i = 0; i < bots.length; i++) {
            var bot = bots[i];

            // Find distance to the closest point on the cube in each dimension
            // https://www.reddit.com/r/adventofcode/comments/add81t/day_23_part_2_still_stuck_on_finding_bots_in/
            // https://stackoverflow.com/questions/5254838/calculating-distance-between-a-point-and-a-rectangular-box-nearest-point
            // https://gdbooks.gitbooks.io/3dcollisions/content/Chapter1/closest_point_aabb.html
            var dx = Math.max(this.xMin - bot.x, 0, bot.x - this.xMax);
            var dy = Math.max(this.yMin - bot.y, 0, bot.y - this.yMax);
            var dz = Math.max(this.zMin - bot.z, 0, bot.z - this.zMax);

            // If Manhattan distance is within the bot's radius, the bot is in range
            if (dx + dy + dz <= bot.r) {
                this.botsInRange++;
            }
        }

        return this.botsInRange;
    }
}

function cutAndCalculate(enclosingCube, bots)
{
    var subcubes = enclosingCube.cutInEight();

    for (var cube of subcubes) {
        cube.countBotsInRange(bots);
    }

    // Find the cube with most bots in range
    subcubes.sort(function(a, b) {
        return b.botsInRange - a.botsInRange;
    });

    // Return all subcubes with maximum bots in range
    var winning = subcubes[0].botsInRange;
    return subcubes.filter(function(c) {
        return (c.botsInRange == winning);
    });
}

function reduce(cubes, bots)
{
    var results = Array();

    for (cube of cubes) {
        if (cube.xMax - cube.xMin > 2 || cube.yMax - cube.yMin > 2 || cube.zMax - cube.zMin > 2) {
            var subcubes = cutAndCalculate(cube, bots);
            var reduced = reduce(subcubes, bots);
            results = results.concat(reduced);
        } else {
            console.log(cube);
            results.push(cube);
        }
    }

    if (results.length == 0) {
        return [];
    }

    // At this point, result should contain the best subcubes of every cube.  Only keep the ones with highest count.
    results.sort(function(a, b) {
        return b.botsInRange - a.botsInRange;
    });

    var winning = results[0].botsInRange;
    var filtered = results.filter(function(c) {
        return (c.botsInRange == winning);
    });
    return filtered;
}

async function main()
{
    var bots = await processLineByLine();

    // Part one
    var bot = findLargestRadius(bots);
    var count = countInRange(bots, bot);
    console.log(count + ' are in range.');

    // Part two
    var enclosingCube = getEnclosingCube(bots);

    var winningCubes = reduce([enclosingCube], bots);
    console.log(winningCubes);

    /*
    do {
        enclosingCube = cutAndCalculate(enclosingCube, bots);
        // console.log(enclosingCube);
    } while (enclosingCube.xMax - enclosingCube.xMin > 1 || enclosingCube.yMax - enclosingCube.yMin > 1 || enclosingCube.zMax - enclosingCube.zMin > 1);
    */
    // The winning subcube is logged for each iteration.  Since the subcubes always get smaller, what I did then is find
    // the largest winning subcube with the maximum number of botsInRange (the last dozen or so had the same number of
    // botsInRange) and find the coordinate closest to 0, 0, 0 in that cube by hand.
}

main();
