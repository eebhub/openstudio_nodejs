#! /usr/bin/env node

var sqlite3 = spawn = require('child_process').spawn;

// run energyplus with child process 
var runenergyplus = spawn('runenergyplus', 
    ['baseline.idf', '/usr/local/EnergyPlus-8-0-0/WeatherData/USA_CA_San.Francisco.Intl.AP.724940_TMY3.epw']);

// print output to stdout
runenergyplus.stdout.on('data', 
    function (data) {
        console.log('stdout: ' + data);
    }
)