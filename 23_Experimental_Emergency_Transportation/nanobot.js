
// Copyright 2019 Max Sprauer
// This is Node.js code.  I'm using 10.15.0.  This was all a mistake.

const fs = require('fs');
const readline = require('readline');

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

// I guess this is technically a hexahedron, because the sides are not equal length.
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

        // This can potentially add duplicate cubes.
        cubes.push(new Cube(this.xMin, this.xMin + halfX, this.yMin, this.yMin + halfY, this.zMin,         this.zMin + halfZ));
        cubes.push(new Cube(this.xMin, this.xMin + halfX, this.yMin, this.yMin + halfY, this.zMax - halfZ, this.zMax));
        cubes.push(new Cube(this.xMin, this.xMin + halfX, this.yMax - halfY, this.yMax, this.zMin,         this.zMin + halfZ));
        cubes.push(new Cube(this.xMin, this.xMin + halfX, this.yMax - halfY, this.yMax, this.zMax - halfZ, this.zMax    ));
        cubes.push(new Cube(this.xMax - halfX, this.xMax, this.yMin, this.yMin + halfY, this.zMin, this.zMin + halfZ));
        cubes.push(new Cube(this.xMax - halfX, this.xMax, this.yMin, this.yMin + halfY, this.zMax - halfZ, this.zMax));
        cubes.push(new Cube(this.xMax - halfX, this.xMax, this.yMax - halfY, this.yMax, this.zMin, this.zMin + halfZ));
        cubes.push(new Cube(this.xMax - halfX, this.xMax, this.yMax - halfY, this.yMax, this.zMax - halfZ, this.zMax));

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

    if (xMin !== undefined) {
        this.volume = this.getVolume();
        this.distance = this.maxCubeDistToOrigin();
    }
}

// A queue of Cube objects
function Queue() {
    this.queue = Array();

    this.addCubes = function(cubes)
    {
        this.queue = this.queue.concat(cubes);
    }

    this.sortCubes = function()
    {
        // Higher bots in radius comes before lower.
        // Smaller size comes before larger.
        // Closer to origin comes before farther.
        this.queue = this.queue.sort(function(a, b) {
            if (b.botsInRange != a.botsInRange) {
                return b.botsInRange - a.botsInRange;
            }

            if (b.volume != a.volume) {
                return b.volume - a.volume;
            }

            return a.distance - b.distance;
        });
    }

    this.getNext = function()
    {
        return this.queue.shift();
    }

    this.print = function()
    {
        console.log("#\tBiR\tVolume\tDist\n");
        for (var i = 0; i < this.queue.length; i++) {
            console.log((i + 1) + "\t" + this.queue[i].botsInRange + "\t" + this.queue[i].volume + "\t" + this.queue[i].distance);
        }
        console.log("\n");
    }
}

function cutAndCalculate(enclosingCube, bots)
{
    var subcubes = enclosingCube.cutInEight();

    for (var cube of subcubes) {
        cube.countBotsInRange(bots);
    }

    return subcubes;
}

async function main()
{
    var bots = await processLineByLine('input.txt');

    // Part one
    var bot = findLargestRadius(bots);
    var count = countInRange(bots, bot);
    console.log(count + ' are in range.');

    // Part two
    var cube = getEnclosingCube(bots);
    var queue = new Queue();

    do {
        var subcubes = cutAndCalculate(cube, bots);
        queue.addCubes(subcubes);
        queue.sortCubes();
        // queue.print();
        cube = queue.getNext();
    } while (cube.getVolume() > 1);

    console.log(cube);
}

main();




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