var sqlite3 = require('sqlite3').verbose();
var database = '../test/Output/baseline.sql';
var fs = require('fs');
var number_format = require('./number_format.js');


//SQL to JSON functions
//Get the Energy Use Data
function getEnergyUse (sqlFile) {

    var db = new sqlite3.Database(sqlFile);
    var gets = {	electricity: {
              interiorLights: [],
            exteriorLights: [],
            interiorEquipment: [],
            exteriorEquipment: [],
            fans: [],
            pumps: [],
            heating: [],
            cooling: [],
            heatRejection: [],
            humidifier: [],
            heatRecovery: [],
            waterSystems: [],
            cogeneration: []
          }, naturalGas: {
            interiorEquipment: [],
            exteriorEquipment: [],
            heating: [],
            cooling: [],
            waterSystems: [],
            cogeneration: []
          }
        };

  var stmt = 	'Select columnname, value, units ' +
         'From tabulardatawithstrings ' +
         'Where (reportname Like "%ELECTRICITY MONTHLY%" Or reportname Like "%NATURAL GAS MONTHLY%")' +
         'And value <> "" ';

    // query statement
  db.serialize(function() {
      db.each(stmt, function(err, row) {
          //gets.push({values: parseFloat(row.value);
          switch(row.columnname) {
            case "INTERIORLIGHTS:ELECTRICITY":
              gets.electricity.interiorLights.push(parseFloat(row.value));
            break;
            case "EXTERIORLIGHTS:ELECTRICITY":
              gets.electricity.exteriorLights.push(parseFloat(row.value));
            break;
            case "INTERIOREQUIPMENT:ELECTRICITY":
              gets.electricity.interiorEquipment.push(parseFloat(row.value));
            break;
            case "EXTERIOREQUIPMENT:ELECTRICITY":
              gets.electricity.exteriorEquipment.push(parseFloat(row.value));
            break;
            case "FANS:ELECTRICITY":
              gets.electricity.fans.push(parseFloat(row.value));
            break;
            case "PUMPS:ELECTRICITY":
              gets.electricity.pumps.push(parseFloat(row.value));
            break;
            case "HEATING:ELECTRICITY":
              gets.electricity.heating.push(parseFloat(row.value));
            break;
            case "COOLING:ELECTRICITY":
              gets.electricity.cooling.push(parseFloat(row.value));
            break;
            case "HEATREJECTION:ELECTRICITY":
              gets.electricity.heatRejection.push(parseFloat(row.value));
            break;
            case "HUMIDIFIER:ELECTRICITY":
              gets.electricity.humidifier.push(parseFloat(row.value));
            break;
            case "HEATRECOVERY:ELECTRICITY":
              gets.electricity.heatRecovery.push(parseFloat(row.value));
            break;
            case "WATERSYSTEMS:ELECTRICITY":
              gets.electricity.waterSystems.push(parseFloat(row.value));
            break;
            case "COGENERATION:ELECTRICITY":
              gets.electricity.cogeneration.push(parseFloat(row.value));
            break;
            case "INTERIOREQUIPMENT:GAS":
              gets.naturalGas.interiorEquipment.push(parseFloat(row.value));
            break;
            case "EXTERIOREQUIPMENT:GAS":
              gets.naturalGas.exteriorEquipment.push(parseFloat(row.value));
            break;
            case "HEATING:GAS":
              gets.naturalGas.heating.push(parseFloat(row.value));
            break;
            case "COOLING:GAS":
              gets.naturalGas.cooling.push(parseFloat(row.value));
            break;
            case "WATERSYSTEMS:GAS":
              gets.naturalGas.waterSystems.push(parseFloat(row.value));
            break;
            case "COGENERATION:GAS":
              gets.naturalGas.cogeneration.push(parseFloat(row.value));
            break;
            default:
              console.log("The ColumnName \""+row.columnname+"\"is not applied here!");
              //gets.electricity.interiorLights.push(parseFloat(row.value))
            break;
          }
      }, function() {
          // All done fetching records, render response
           console.log(gets)
      })
  })
};

//Get the Energy Intensity Data
function getEnergyIntensity(sqlFile) {

    var db = new sqlite3.Database(sqlFile);
    var gets = {	totalSiteEnergy: {
              endUses: {
                electricity: [],
                naturalGas: [] },
              totalElectricity: 0,
              totalNaturalGas: 0,
              buildingArea: 0,
              totalIntensity: 0,
              conditionedIntensity: 0
          }, totalSourceEnergy: {
            endUses: {
              electricity: [],
              naturalGas: [] },
            totalElectricity: 0,
              totalNaturalGas: 0,
            buildingArea: 0,
            totalIntensity: 0,
            conditionedIntensity: 0
          }
        };

  var stmt = 	'Select tableName, rowName, columnName, value, units ' +
         'From tabulardatawithstrings ' +
         'Where (ReportName Like "%Annual%"  Or ReportName Like "%SourceEnergyEndUse%" ) ' +
         'And (TableName Like "End Uses" Or TableName Like "Site and Source Energy" ' +
         'Or TableName Like "Building Area" Or TableName Like "Source Energy End Use Components Summary") ' +
         'And value <> "" ';

    // query statement
  db.serialize(function() {
      db.each(stmt, function(err, row) {
          //gets.push({values: parseFloat(row.value)

         switch(row.tableName) {

           // Total site annual energy
           case 'End Uses':
             switch(row.columnName) {
               case 'Electricity':
                 if(row.rowName == 'Total End Uses') {
                   gets.totalSiteEnergy.totalElectricity= parseFloat(row.value);
                 } else {
                   gets.totalSiteEnergy.endUses.electricity.push(parseFloat(row.value));
                 }
               break;
               case 'Natural Gas':
                 if(row.rowName == 'Total End Uses') {
                   gets.totalSiteEnergy.totalNaturalGas= parseFloat(row.value);
                 } else {
                   gets.totalSiteEnergy.endUses.naturalGas.push(parseFloat(row.value));
                 }
               break;
               default:
               break;
             }
           break;

           // Total source annual energy
           case 'Source Energy End Use Components Summary':
             switch(row.columnName) {
               case 'Source Electricity':
                 if(row.rowName == 'Total Source Energy End Use Components') {
                   gets.totalSourceEnergy.totalElectricity= parseFloat(row.value);
                 } else {
                   gets.totalSourceEnergy.endUses.electricity.push(parseFloat(row.value));
                 }
               break;
               case 'Source Natural Gas':
              if(row.rowName == 'Total Source Energy End Use Components') {
                   gets.totalSourceEnergy.totalNaturalGas= parseFloat(row.value);
                 } else {
                   gets.totalSourceEnergy.endUses.naturalGas.push(parseFloat(row.value));
                 }
               break;
               default:
               break;
             }
           break;

           // Building area
           case 'Building Area':
             if(row.rowName == 'Total Building Area') {
               gets.totalSiteEnergy.buildingArea = parseFloat(row.value);
               gets.totalSourceEnergy.buildingArea = parseFloat(row.value);
             }
           break;

           // Total site and source energy intensities
           case 'Site and Source Energy':
             if(row.rowName == 'Total Site Energy') {
               // Total or conditioned intensity
               switch(row.columnName) {
                 case 'Energy Per Total Building Area':
                   gets.totalSiteEnergy.totalIntensity = parseFloat(row.value);
                 break;
                 case 'Energy Per Conditioned Building Area':
                   gets.totalSiteEnergy.conditionedIntensity = parseFloat(row.value);
                 break;
                 default:
                 break;
               }
          } else if(row.rowName == 'Total Source Energy') {
            // Total or conditioned intensity
               switch(row.columnName) {
                 case 'Energy Per Total Building Area':
                   gets.totalSourceEnergy.totalIntensity = parseFloat(row.value);
                 break;
                 case 'Energy Per Conditioned Building Area':
                   gets.totalSourceEnergy.conditionedIntensity = parseFloat(row.value);
                 break;
                 default:
                 break;
               }
          }
           break;
         }
      }, function() {
          // All done fetching records, render response
           console.log(gets.totalSourceEnergy)
           console.log(gets.totalSiteEnergy)
      })
  })
}

//Get Energy Cost Data
function getEnergyCost(sqlFile) {

    var db = new sqlite3.Database(sqlFile);
    var gets = {	electricity: {
              energyCharges: [],
            demandCharges: [],
            serviceCharges: [],
            adjustment: [],
            taxes: [],
            total: []
          }, naturalGas: {
            energyCharges: [],
            demandCharges: [],
            serviceCharges: [],
            adjustment: [],
            taxes: [],
            total: []
          }
        };

  var stmt = 	'Select reportForString, rowName, value, units ' +
         'From tabulardatawithstrings ' +
         'Where reportName Like "%Tariff Report%" ' +
         'And value <> "" ';

    // query statement
  db.serialize(function() {
      db.each(stmt, function(err, row) {
          //gets.push({values: parseFloat(row.value)
          if(row.reportForString == "BLDG101_ELECTRIC_RATE") {
            switch(row.rowName) {
              case "EnergyCharges (~~$~~)":
                gets.electricity.energyCharges.push(parseFloat(row.value));
              break;
              case "DemandCharges (~~$~~)":
                gets.electricity.demandCharges.push(parseFloat(row.value));
              break;
              case "ServiceCharges (~~$~~)":
                gets.electricity.serviceCharges.push(parseFloat(row.value));
              break;
              case "Adjustment (~~$~~)":
                gets.electricity.adjustment.push(parseFloat(row.value));
              break;
              case "Taxes (~~$~~)":
                gets.electricity.taxes.push(parseFloat(row.value));
              break;
              case "Total (~~$~~)":
                gets.electricity.total.push(parseFloat(row.value));
              break;
              default:
                //console.log("The ColumnName \""+row.columnname+"\"is not applied here!");
                //gets.electricity.interiorLights.push(parseFloat(row.value))
              break;
           }
          } else {
            switch(row.rowName) {
              case "EnergyCharges (~~$~~)":
                gets.naturalGas.energyCharges.push(parseFloat(row.value));
              break;
              case "DemandCharges (~~$~~)":
                gets.naturalGas.demandCharges.push(parseFloat(row.value));
              break;
              case "ServiceCharges (~~$~~)":
                gets.naturalGas.serviceCharges.push(parseFloat(row.value));
              break;
              case "Adjustment (~~$~~)":
                gets.naturalGas.adjustment.push(parseFloat(row.value));
              break;
              case "Taxes (~~$~~)":
                gets.naturalGas.taxes.push(parseFloat(row.value));
              break;
              case "Total (~~$~~)":
                gets.naturalGas.total.push(parseFloat(row.value));
              break;
              default:
                //console.log("The RowName \""+row.columnname+"\"is not applied here!");
              break;
           }
          }

      }, function() {
          // All done fetching records, render response
           console.log(gets)
      })
  })
}

//getEnergyUse('../test/eem_1.sql');
