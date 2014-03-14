//Dependencies
var eebSqlite3 = require('../library/eeb_sqlite3.js');
var fs = require('fs');


//Starter Test Variables
var database = 'test/Output/baseline.sql';

//SQL Parsing Functions
function getEnergyUseData(sqlFile, fn) {
    var energyUse = {
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
            cooling: []
        },

    };
    eebSqlite3.getValuesByMonthly('END USE ENERGY CONSUMPTION ELECTRICITY MONTHLY', 'Meter', '', '%', database, function(electResults) {
        electResults.forEach(function(row) {
            if (row.curColumnName == 'HEATING:ELECTRICITY') {
                energyUse.electricity.heating.push(row.value);
            }
            else if (row.curColumnName == 'COOLING:ELECTRICITY') {
                energyUse.electricity.cooling.push(row.value);
            }
            else if (row.curColumnName == 'PUMPS:ELECTRICITY') {
                energyUse.electricity.pumps.push(row.value);
            }
            else if (row.curColumnName == 'FANS:ELECTRICITY') {
                energyUse.electricity.fans.push(row.value);
            }
            else if (row.curColumnName == 'INTERIORLIGHTS:ELECTRICITY') {
                energyUse.electricity.interiorLighting.push(row.value);
            }
            else if (row.curColumnName == 'INTERIOREQUIPMENT:ELECTRICITY') {
                energyUse.electricity.interiorEquipment.push(row.value);
            }
            else if (row.curColumnName == 'EXTERIORLIGHTS:ELECTRICITY') {
                energyUse.electricity.exteriorLighting.push(row.value);
            }
            else if (row.curColumnName == 'HEATREJECTION:ELECTRICITY') {
                energyUse.electricity.heatRejection.push(row.value);
            }
            else if (row.curColumnName == 'EXTERIOREQUIPMENT:ELECTRICITY') {
                energyUse.electricity.exteriorEquipment.push(row.value);
            }
            else if (row.curColumnName == 'HUMIDIFIER:ELECTRICITY') {
                energyUse.electricity.humidification.push(row.value);
            }
            else if (row.curColumnName == 'HEATRECOVERY:ELECTRICITY') {
                energyUse.electricity.heatingRecovery.push(row.value);
            }
            else if (row.curColumnName == 'WATERSYSTEMS:ELECTRICITY') {
                energyUse.electricity.waterSystems.push(row.value);
            }
            else if (row.curColumnName == 'COGENERATION:ELECTRICITY') {
                energyUse.electricity.generation.push(row.value);
            }
        });

        eebSqlite3.getValuesByMonthly('END USE ENERGY CONSUMPTION NATURAL GAS MONTHLY', 'Meter', '', '%', database, function(gasResults) {
            gasResults.forEach(function(row) {
                if (row.curColumnName == 'HEATING:GAS') {
                    energyUse.gas.heating.push(row.value);
                }
                else if (row.curColumnName == 'COOLING:GAS') {
                    energyUse.gas.cooling.push(row.value);
                }
                else if (row.curColumnName == 'INTERIOREQUIPMENT:GAS') {
                    energyUse.gas.interiorEquipment.push(row.value);
                }
                else if (row.curColumnName == 'WATERSYSTEMS:GAS') {
                    energyUse.gas.waterSystems.push(row.value);
                }
                else if (row.curColumnName == 'COGENERATION:GAS') energyUse
            });
            fn(energyUse);

        });
    });
}

function getEnergyIntensityData(sqlFile, fn) {
    var energyIntensity = {
        totalEnergy: [],
        siteEnergy: [],
        sourceEnergy: [],
        area: []
    };

    eebSqlite3.getValues('AnnualBuildingUtilityPerformanceSummary', 'Entire Facility', 'Site and Source Energy', '%', database, function(results) {
        energyIntensity.totalEnergy = results;
        eebSqlite3.getValues('AnnualBuildingUtilityPerformanceSummary', 'Entire Facility', 'End Uses', 'kBtu', database, function(results) {
            energyIntensity.siteEnergy = results;
            eebSqlite3.getValues('SourceEnergyEndUseComponentsSummary', 'Entire Facility', 'Source Energy End Use Components Summary', 'kBtu', database, function(results) {
                energyIntensity.sourceEnergy = results;
                eebSqlite3.getValues('AnnualBuildingUtilityPerformanceSummary', 'Entire Facility', 'Building Area', '%', database, function(results) {
                    energyIntensity.area = results;
                    fs.writeFile("energy.json", energyIntensity, null, 4);
                    fn(energyIntensity);
                });
            });

        });
    });
}
function getEnergyValues(buildingObject, type , rowName, colName){
    buildingObject.type.forEach(function(row){
        if(row.curRowName == rowName && row.curColumnName == colName){
            return row.value;
        }
    });
}
//Routing
module.exports = {
    getEnergyUse: function(request, response) {

        getEnergyUseData(database, function(energyUse) {

            response.render('energy-use', {
                energy: energyUse
            });

        });
    },
    getEnergyIntensity: function(request, response) {
        getEnergyIntensityData(database, function(energyIntensity) {
            console.log(energyIntensity);
            //var totalElectricityUse = getEnergyValues(energyIntensity, "totalEnergy", "Total End Uses", "Electricity");
            fs.writeFile("energys.json", JSON.stringify(energyIntensity), null, 4), function(err){
                if(err) throw err;
            };
            
            response.render('energy-intensity', {
                energyIntensity: energyIntensity
                //totalElectricityUse:totalElectricityUse
            });
        });

    }
};
