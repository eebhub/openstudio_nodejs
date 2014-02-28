var eebSqlite3 = require('../lib/eeb_sqlite3.js');
var database = 'Output/baseline.sql';
var fs = require('fs');


var energy = {electricity:[], gas:[]};

eebSqlite3.getValuesByMonthly('END USE ENERGY CONSUMPTION ELECTRICITY MONTHLY', 'Meter', '', '%', database, function(results) {
    energy.electricity = results;

    eebSqlite3.getValuesByMonthly('END USE ENERGY CONSUMPTION NATURAL GAS MONTHLY', 'Meter', '', '%', database, function(results) {
        energy.gas[0] = results;
        eebSqlite3.getValuesByMonthly('END USE ENERGY CONSUMPTION NATURAL GAS MONTHLY', 'Meter', '', '%', database, function(results) {
            energy.gas[1] = results;
            fs.writeFile("energy.json", JSON.stringify(energy), function(err) {
                if (err) console.log("Error");
            });

        });

    });
});
