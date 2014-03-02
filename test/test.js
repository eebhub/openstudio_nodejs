var eebSqlite3 = require('../lib/eeb_sqlite3.js');
var database = 'Output/baseline.sql';
var fs = require('fs');


var energy = {
    electricity: {
        pumps: [],
        fans: [],
        cooling: [],
        interiorLighting: [],
        interiorEquipment: [],
        heating: [],
        exteriorLighting: [],
        heatRejection: [],
        humidification: [],
        heatingRecovery: [],
        waterSystems: [],
        exteriorEquipment: [],
        generation: []
    },
    gas: {

        interiorEquipment: [],
        heating: [],
        waterSystems: [],
        generation: [],
        cooling:[]
    },

};


eebSqlite3.getValuesByMonthly('END USE ENERGY CONSUMPTION ELECTRICITY MONTHLY', 'Meter', '', '%', database, function(electResults) {
    electResults.forEach(function(row) {
        if (row.curColumnName == 'HEATING:ELECTRICITY') {
            energy.electricity.heating.push(row.value);
        }
        else if (row.curColumnName == 'COOLING:ELECTRICITY') {
            energy.electricity.cooling.push(row.value);
        }
        else if (row.curColumnName == 'PUMPS:ELECTRICITY') {
            energy.electricity.pumps.push(row.value);
        }
        else if (row.curColumnName == 'FANS:ELECTRICITY') {
            energy.electricity.fans.push(row.value);
        }
        else if (row.curColumnName == 'INTERIORLIGHTS:ELECTRICITY') {
            energy.electricity.interiorLighting.push(row.value);
        }
        else if (row.curColumnName == 'INTERIOREQUIPMENT:ELECTRICITY') {
            energy.electricity.interiorEquipment.push(row.value);
        }
        else if (row.curColumnName == 'EXTERIORLIGHTS:ELECTRICITY') {
            energy.electricity.exteriorLighting.push(row.value);
        }
        else if (row.curColumnName == 'HEATREJECTION:ELECTRICITY') {
            energy.electricity.heatRejection.push(row.value);
        }
        else if (row.curColumnName == 'EXTERIOREQUIPMENT:ELECTRICITY') {
            energy.electricity.exteriorEquipment.push(row.value);
        }
        else if (row.curColumnName == 'HUMIDIFIER:ELECTRICITY') {
            energy.electricity.humidification.push(row.value);
        }
        else if (row.curColumnName == 'HEATRECOVERY:ELECTRICITY') {
            energy.electricity.heatingRecovery.push(row.value);
        }
        else if (row.curColumnName == 'WATERSYSTEMS:ELECTRICITY') {
            energy.electricity.waterSystems.push(row.value);
        }
        else if(row.curColumnName == 'COGENERATION:ELECTRICITY'){
            energy.electricity.generation.push(row.value);
        }
    });
   
    eebSqlite3.getValuesByMonthly('END USE ENERGY CONSUMPTION NATURAL GAS MONTHLY', 'Meter', '', '%', database, function(gasResults) {
      gasResults.forEach(function(row){
                if (row.curColumnName == 'HEATING:GAS'){
                    energy.gas.heating.push(row.value);
                }else if(row.curColumnName == 'COOLING:GAS'){
                     energy.gas.cooling.push(row.value);
                }else if(row.curColumnName == 'INTERIOREQUIPMENT:GAS'){
                     energy.gas.interiorEquipment.push(row.value);
                }else if(row.curColumnName == 'WATERSYSTEMS:GAS'){
                     energy.gas.waterSystems.push(row.value);
                }else if(row.curColumnName == 'COGENERATION:GAS'){
                     energy.gas.generation.push(row.value);
                }
            });
        
        fs.writeFile("energyFull.json", JSON.stringify(energy, null, 4), function(err) {
            if (err) console.log("Error");
        });



    });
});
