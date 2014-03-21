var sqlite3 = require('sqlite3').verbose();


module.exports.sqlToJSON = sqlToJSON;

//SQL to JSON functions
function sqlToJSON(sqlFile, fn){
  //Initialize Database
  var db = new sqlite3.Database(sqlFile);
  //JSON Structure
  var output = {
    energyUse: {
      electricity: {
        interiorLights: {},
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
      },
      naturalGas: {
        interiorEquipment: [],
        exteriorEquipment: [],
        heating: [],
        cooling: [],
        waterSystems: [],
        cogeneration: []
      }
    },
    energyIntensity:
    {
      totalSiteEnergy:{
        endUses: {
          electricity: [],
          naturalGas: []
        },
        totalElectricity: 0,
        totalNaturalGas: 0,
        buildingArea: 0,
        totalIntensity: 0,
        conditionedIntensity: 0
      },
      totalSourceEnergy:{
        endUses: {
          electricity: [],
          naturalGas: []
        },
        totalElectricity: 0,
        totalNaturalGas: 0,
        buildingArea: 0,
        totalIntensity: 0,
        conditionedIntensity: 0
      },
    },
    energyCost:{
      electricity: {
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
    }
  };
  //SQL Statements (Stmt)
  var energyUseSqlStmt = 'Select columnname, value, units, rowname ' +
         'From tabulardatawithstrings ' +
         'Where (reportname Like "%ELECTRICITY MONTHLY%" Or reportname Like "%NATURAL GAS MONTHLY%")' +
         'And value <> "" ',
      energyIntensitySqlStmt = 'Select tableName, rowName, columnName, value, units ' +
             'From tabulardatawithstrings ' +
             'Where (ReportName Like "%Annual%"  Or ReportName Like "%SourceEnergyEndUse%" ) ' +
             'And (TableName Like "End Uses" Or TableName Like "Site and Source Energy" ' +
             'Or TableName Like "Building Area" Or TableName Like "Source Energy End Use Components Summary") ' +
             'And value <> "" ',
      energyCostSqlStmt = 'Select reportForString, rowName, value, units ' +
             'From tabulardatawithstrings ' +
             'Where reportName Like "%Tariff Report%" ' +
             'And value <> "" ';
  //Start SQL Queries
  db.serialize(function() {
    //Get Energy Use Data
    db.each(energyUseSqlStmt, function(err, row){
        
      switch(row.columnname) {
        case "INTERIORLIGHTS:ELECTRICITY":
              month = new Object();
              
              var interLights = 
          output.energyUse.electricity.interiorLights.month.value = parseFloat(row.value);
              
          break;
        case "EXTERIORLIGHTS:ELECTRICITY":
          output.energyUse.electricity.exteriorLights.push(parseFloat(row.value));
          break;
        case "INTERIOREQUIPMENT:ELECTRICITY":
          output.energyUse.electricity.interiorEquipment.push(parseFloat(row.value));
          break;
        case "EXTERIOREQUIPMENT:ELECTRICITY":
          output.energyUse.electricity.exteriorEquipment.push(parseFloat(row.value));
          break;
        case "FANS:ELECTRICITY":
          output.energyUse.electricity.fans.push(parseFloat(row.value));
          break;
        case "PUMPS:ELECTRICITY":
          output.energyUse.electricity.pumps.push(parseFloat(row.value));
          break;
        case "HEATING:ELECTRICITY":
          output.energyUse.electricity.heating.push(parseFloat(row.value));
          break;
        case "COOLING:ELECTRICITY":
          output.energyUse.electricity.cooling.push(parseFloat(row.value));
          break;
        case "HEATREJECTION:ELECTRICITY":
          output.energyUse.electricity.heatRejection.push(parseFloat(row.value));
          break;
        case "HUMIDIFIER:ELECTRICITY":
          output.energyUse.electricity.humidifier.push(parseFloat(row.value));
          break;
        case "HEATRECOVERY:ELECTRICITY":
          output.energyUse.electricity.heatRecovery.push(parseFloat(row.value));
          break;
        case "WATERSYSTEMS:ELECTRICITY":
          output.energyUse.electricity.waterSystems.push(parseFloat(row.value));
          break;
        case "COGENERATION:ELECTRICITY":
          output.energyUse.electricity.cogeneration.push(parseFloat(row.value));
          break;
        case "INTERIOREQUIPMENT:GAS":
          output.energyUse.naturalGas.interiorEquipment.push(parseFloat(row.value));
          break;
        case "EXTERIOREQUIPMENT:GAS":
          output.energyUse.naturalGas.exteriorEquipment.push(parseFloat(row.value));
          break;
        case "HEATING:GAS":
            console.log(row);
            output.energyUse.naturalGas.heating.push(parseFloat(row.value));
            break;
        case "COOLING:GAS":
          output.energyUse.naturalGas.cooling.push(parseFloat(row.value));
        break;
        case "WATERSYSTEMS:GAS":
          output.energyUse.naturalGas.waterSystems.push(parseFloat(row.value));
          break;
        case "COGENERATION:GAS":
          output.energyUse.naturalGas.cogeneration.push(parseFloat(row.value));
          break;
        default:
          console.log("The ColumnName \""+row.columnname+"\"is not applied here!");
          break;
      }

    }, function(){console.log("Use Finished")});
    //Get Energy Instensity for SQL File
    db.each(energyIntensitySqlStmt, function(err, row){
      switch(row.tableName) {

        // Total site annual energy
        case 'End Uses':
          switch(row.columnName) {
            case 'Electricity':
              if(row.rowName == 'Total End Uses') {
                output.energyIntensity.totalSiteEnergy.totalElectricity= parseFloat(row.value);
              } else {
                output.energyIntensity.totalSiteEnergy.endUses.electricity.push(parseFloat(row.value));
              }
            break;
            case 'Natural Gas':
              if(row.rowName == 'Total End Uses') {
                output.energyIntensity.totalSiteEnergy.totalNaturalGas= parseFloat(row.value);
              } else {
                output.energyIntensity.totalSiteEnergy.endUses.naturalGas.push(parseFloat(row.value));
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
                output.energyIntensity.totalSourceEnergy.totalElectricity= parseFloat(row.value);
              } else {
                output.energyIntensity.totalSourceEnergy.endUses.electricity.push(parseFloat(row.value));
              }
            break;
            case 'Source Natural Gas':
              if(row.rowName == 'Total Source Energy End Use Components') {
                output.energyIntensity.totalSourceEnergy.totalNaturalGas= parseFloat(row.value);
              } else {
                output.energyIntensity.totalSourceEnergy.endUses.naturalGas.push(parseFloat(row.value));
              }
              break;
            default:
              break;
          }
        break;

        // Building area
        case 'Building Area':
          if(row.rowName == 'Total Building Area') {
            output.energyIntensity.totalSiteEnergy.buildingArea = parseFloat(row.value);
            output.energyIntensity.totalSourceEnergy.buildingArea = parseFloat(row.value);
          }
        break;

        // Total site and source energy intensities
        case 'Site and Source Energy':
          if(row.rowName == 'Total Site Energy') {
            // Total or conditioned intensity
            switch(row.columnName) {
              case 'Energy Per Total Building Area':
                output.energyIntensity.totalSiteEnergy.totalIntensity = parseFloat(row.value);
                break;
              case 'Energy Per Conditioned Building Area':
                output.energyIntensity.totalSiteEnergy.conditionedIntensity = parseFloat(row.value);
                break;
              default:
                break;
            }
       } else if(row.rowName == 'Total Source Energy') {
         // Total or conditioned intensity
            switch(row.columnName) {
              case 'Energy Per Total Building Area':
                output.energyIntensity.totalSourceEnergy.totalIntensity = parseFloat(row.value);
                break;
              case 'Energy Per Conditioned Building Area':
                output.energyIntensity.totalSourceEnergy.conditionedIntensity = parseFloat(row.value);
                break;
              default:
                break;
            }
       }
        break;
      }
    },

    function(){console.log("IntensityFinished")});
    //Get Energy Cost Data
    db.each(energyCostSqlStmt, function(err, row){
      if(row.reportForString.search("ELECTRIC") != -1) {
        switch(row.rowName) {
          case "EnergyCharges (~~$~~)":
            output.energyCost.electricity.energyCharges.push(parseFloat(row.value));
            break;
          case "DemandCharges (~~$~~)":
            output.energyCost.electricity.demandCharges.push(parseFloat(row.value));
            break;
          case "ServiceCharges (~~$~~)":
            output.energyCost.electricity.serviceCharges.push(parseFloat(row.value));
            break;
          case "Adjustment (~~$~~)":
            output.energyCost.electricity.adjustment.push(parseFloat(row.value));
            break;
          case "Taxes (~~$~~)":
            output.energyCost.electricity.taxes.push(parseFloat(row.value));
            break;
          case "Total (~~$~~)":
            output.energyCost.electricity.total.push(parseFloat(row.value));
            break;
          default:
            //console.log("The ColumnName \""+row.columnname+"\"is not applied here!");
            break;
       }
      } else {
        switch(row.rowName) {
          case "EnergyCharges (~~$~~)":
            output.energyCost.naturalGas.energyCharges.push(parseFloat(row.value));
            break;
          case "DemandCharges (~~$~~)":
            output.energyCost.naturalGas.demandCharges.push(parseFloat(row.value));
            break;
          case "ServiceCharges (~~$~~)":
            output.energyCost.naturalGas.serviceCharges.push(parseFloat(row.value));
            break;
          case "Adjustment (~~$~~)":
            output.energyCost.naturalGas.adjustment.push(parseFloat(row.value));
            break;
          case "Taxes (~~$~~)":
            output.energyCost.naturalGas.taxes.push(parseFloat(row.value));
            break;
          case "Total (~~$~~)":
            output.energyCost.naturalGas.total.push(parseFloat(row.value));
            break;
          default:
            //console.log("The RowName \""+row.columnname+"\"is not applied here!");
            break;
       }
      }

    }, function(){
      console.log("Cost Finished");
      //Callback
      fn(output);
    });
  });
  //Close Database
  db.close();

}


