
// Copyright 2019 Max Sprauer
// This is Node.js code.  I'm using 10.15.0.  This was all a mistake.

const fs = require('fs');
const readline = require('readline');
var winningPoint = null;

// https://stackoverflow.com/questions/38942354/how-to-return-values-from-an-event-handler-in-a-promise
function processLineByLine(filename) {
    return new Promise(function(resolve, reject) {
        const fileStream = fs.createReadStream(filename);
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

    for (var i = 0; i < bots.length; i++) {
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

        var xDiff = this.xMax - this.xMin;
        var halfX = (xDiff > 1) ? Math.ceil((xDiff) / 2) : 0;
        var yDiff = this.yMax - this.yMin;
        var halfY = (yDiff > 1) ? Math.ceil((yDiff) / 2) : 0;
        var zDiff = this.zMax - this.zMin;
        var halfZ = (zDiff > 1) ? Math.ceil((zDiff) / 2) : 0;

        // We want all cubes to be the same volume, even if they overlap by one
        // this.xMin + halfX - 1
        /*
        cubes.push(new Cube(this.xMin, this.xMin + halfX, this.yMin, this.yMin + halfY, this.zMin,         this.zMin + halfZ));
        cubes.push(new Cube(this.xMin, this.xMin + halfX, this.yMin, this.yMin + halfY, this.zMin + halfZ, this.zMax));
        cubes.push(new Cube(this.xMin, this.xMin + halfX, this.yMin + halfY, this.yMax, this.zMin,         this.zMin + halfZ));
        cubes.push(new Cube(this.xMin, this.xMin + halfX, this.yMin + halfY, this.yMax, this.zMin + halfZ, this.zMax    ));
        cubes.push(new Cube(this.xMin + halfX, this.xMax, this.yMin, this.yMin + halfY, this.zMin, this.zMin + halfZ));
        cubes.push(new Cube(this.xMin + halfX, this.xMax, this.yMin, this.yMin + halfY, this.zMin + halfZ, this.zMax));
        cubes.push(new Cube(this.xMin + halfX, this.xMax, this.yMin + halfY, this.yMax, this.zMin, this.zMin + halfZ));
        cubes.push(new Cube(this.xMin + halfX, this.xMax, this.yMin + halfY, this.yMax, this.zMin + halfZ, this.zMax));
        */

        // This can potentially add duplicate cubes.
        cubes.push(new Cube(this.xMin, this.xMin + halfX, this.yMin, this.yMin + halfY, this.zMin,         this.zMin + halfZ));
        cubes.push(new Cube(this.xMin, this.xMin + halfX, this.yMin, this.yMin + halfY, this.zMax - halfZ, this.zMax));
        cubes.push(new Cube(this.xMin, this.xMin + halfX, this.yMax - halfY, this.yMax, this.zMin,         this.zMin + halfZ));
        cubes.push(new Cube(this.xMin, this.xMin + halfX, this.yMax - halfY, this.yMax, this.zMax - halfZ, this.zMax    ));
        cubes.push(new Cube(this.xMax - halfX, this.xMax, this.yMin, this.yMin + halfY, this.zMin, this.zMin + halfZ));
        cubes.push(new Cube(this.xMax - halfX, this.xMax, this.yMin, this.yMin + halfY, this.zMax - halfZ, this.zMax));
        cubes.push(new Cube(this.xMax - halfX, this.xMax, this.yMax - halfY, this.yMax, this.zMin, this.zMin + halfZ));
        cubes.push(new Cube(this.xMax - halfX, this.xMax, this.yMax - halfY, this.yMax, this.zMax - halfZ, this.zMax));

        // Get rid of any with 0 dimensions
        cubes = cubes.filter(function(c) {
            return (c.getVolume() > 0);
        });

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
    
    this.isPoint = function () {
        return (this.xMax - this.xMin == 0 && this.yMax - this.yMin == 0 && this.zMax - this.zMin == 0);
    }

    this.getVolume = function () {
        return (this.xMax - this.xMin + 1) * (this.yMax - this.yMin + 1) * (this.zMax - this.zMin + 1);
    }

    // Only works for points.
    this.distanceToOrigin = function () {
        return Math.abs(this.xMax) + Math.abs(this.yMax) + Math.abs(this.zMax);
    }

    // Returns the distance from the farthest point on the cube
    this.maxCubeDistToOrigin = function () {
        return Math.max(Math.abs(this.xMin), Math.abs(this.xMax)) +
            Math.max(Math.abs(this.yMin), Math.abs(this.yMax)) +
            Math.max(Math.abs(this.zMin), Math.abs(this.zMax));
    }

    this.minCubeDistToOrigin = function () {
        return Math.min(Math.abs(this.xMin), Math.abs(this.xMax)) +
            Math.min(Math.abs(this.yMin), Math.abs(this.yMax)) +
            Math.min(Math.abs(this.zMin), Math.abs(this.zMax));
    }

    this.printPoint = function () {
        return this.xMax + ', ' + this.yMax + ', ' + this.zMax;
    }

    this.minDimension = function () {
        return Math.min(this.xMax - this.xMin, this.yMax - this.yMin, this.zMax - this.zMin);
    }


    /*
    if (xMin !== undefined) {
        this.volume = this.getVolume();
        this.mcdto = this.maxCubeDistToOrigin();

    }
    */
    // if (this.getVolume() == 0) {
        //console.log("New cube of volume: " + this.getVolume());
    // }
}

function getWinners(cubes)
{
    var winners = Array();

    if (cubes.length > 0) {

        // Find the cube with most bots in range
        cubes.sort(function(a, b) {
            return b.botsInRange - a.botsInRange;
        });

// ASSERT all cubes are the same size

        // Return all subcubes with maximum bots in range

        // The subcube's bots in range is the maximum of any point in the subcube.  If multiple subcubes are tied, pick
        // the one closest to the origin.

        var winning = cubes[0].botsInRange;
        if (winning > 0) {
            winners = cubes.filter(function(c) {
                return (c.botsInRange == winning);
            });
        }

        // The closest point of the cube must be closer than the winning point, otherwise no point in the
        // cube will beat the winning point
        if (winningPoint) {
            winners = winners.filter(function(c) {
                return (c.minCubeDistToOrigin() <= winningPoint.distanceToOrigin() && c.botsInRange >= winningPoint.botsInRange);
            });
        }

                /*
        winners.sort(function(a, b) {
            return a.maxCubeDistToOrigin() - b.maxCubeDistToOrigin();
        });

        winners = winners.slice(0, 1);
        */
       // console.log(cubes);

        /*

        // Find the cubes of each size with most bots in range.  For single points, only keep the ones
        // closest to the origin.
        // TODO Should be able to filter out any cube larger or equal volume than the current with fewer bots in range
        var winning = Array();
        var minDist = Infinity;
        for (var cube of cubes) {
            var vol = cube.getVolume();
            if (vol in winning) {
                if (cube.botsInRange > winning[vol]) {
                    winning[vol] = cube.botsInRange;
                }
            } else {
                winning[vol] = cube.botsInRange;
            }

            if (cube.isPoint()) {
                if (cube.distanceToOrigin() < minDist) {
                    minDist = cube.distanceToOrigin();
                }
            }
        }

        winners = cubes.filter(function(c) {
            if (c.botsInRange == winning[c.getVolume()]) {
                if (c.isPoint()) {
                    if (c.distanceToOrigin() != minDist) {
                        return false;
                    }
                }

                return true;
            }

            return false;
        });
    */
    }

    // console.log("Number of winners: " +winners.length);
    return winners;
}

function cutAndCalculate(enclosingCube, bots)
{
    var subcubes = enclosingCube.cutInEight();

    for (var cube of subcubes) {
        cube.countBotsInRange(bots);
    }

    var winners = getWinners(subcubes);
    return winners;
}

// Take an array of cubes, return the winning cubes
function reduce(cubes, bots, depth)
{
    var results = Array();

    if (depth <= 23) {
        var str = depth + " Starting reduce with cubes of volume:";
        for (var cube of cubes) {
            str += ' ' + cube.getVolume() + ' [' + cube.botsInRange + '] ';
        }

        console.log(String(' ').repeat(depth * 2) + str);
    }

    for (var cube of cubes) {
        //console.log('reduce on cube of volume ' + cube.getVolume() + ' and botsInRange ' + cube.botsInRange);

        if (cube.getVolume() == 672) {
            // console.log('Got a six hundred seventy-two\'er');
        }

        if (cube.isPoint()) {
        // if (cube.xMax - cube.xMin <= 2 || cube.yMax - cube.yMin <= 2 || cube.zMax - cube.zMin <= 2) {
            // results.push(cube);
            if (!winningPoint
                    || (cube.botsInRange > winningPoint.botsInRange)
                    || (cube.botsInRange == winningPoint.botsInRange && cube.distanceToOrigin() < winningPoint.distanceToOrigin())) {
                console.log("New winning point: " + cube.printPoint() + ' dist: ' + cube.distanceToOrigin() + ' (' + cube.botsInRange + ')');
                winningPoint = cube;
            }
    //    } else if (cube.getVolume() < 100) {
            // console.log(String(' ').repeat(depth) + "Stopping on cube of volume " + cube.getVolume());
    //        results.push(cube);
        } else {
            if (cube.getVolume() < 1000) {
                // console.log(cube.getVolume());
            }

            // if (cube.minDimension() > 2) {
                var subcubes = cutAndCalculate(cube, bots);
                //console.log('reduce on cube of volume ' + cube.getVolume() + ' and botsInRange ' + cube.botsInRange);

                var reduced = reduce(subcubes, bots, depth + 1);
                results = results.concat(reduced);
            // } else {
            //    results.push(cube);
            //}

        }
    }

    if (results.length == 0) {
        return [];
    }

    var winners = getWinners(results);
    // console.log(String(' ').repeat(depth) + 'Returning winners of length: ' + winners.length);

    return winners;
}

function test_cutInEight()
{
    var cubes = new Cube(1, 5, 1, 5, 1, 5).cutInEight();
    //console.log(cubes);
    cubes = new Cube(1, 4, 1, 4, 1, 4).cutInEight();
    //console.log(cubes);
    cubes = new Cube(1, 2, 1, 2, 1, 2).cutInEight();
    console.log(cubes);

    var cubes = new Cube(59129130, 59129132, 10004959, 10004960, 32465446, 32465447).cutInEight();
    console.log(cubes);
}

async function main()
{
    var bots = await processLineByLine('input.txt');

    // Part one
    var bot = findLargestRadius(bots);
    var count = countInRange(bots, bot);
    console.log(count + ' are in range.');

    // Part two
    //test_cutInEight();
    //return;

    var enclosingCube = getEnclosingCube(bots);
    console.log(enclosingCube);
    console.log(enclosingCube.getVolume());
    // return;


    var winningCubes = reduce([enclosingCube], bots, 0);
    console.log(winningCubes);
    console.log(winningPoint);

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


async function test_one()
{
    var bots = await processLineByLine('test1.txt');
    var enclosingCube = getEnclosingCube(bots);
    var winningCubes = reduce([enclosingCube], bots, 0);
    console.log(winningCubes);
    console.log(winningPoint);
}


main();
//test_one();