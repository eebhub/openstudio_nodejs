#!/usr/bin/env node

// modules
var sqlite3 = require('sqlite3').verbose();

// example of database
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
        	//gets.push({values: parseFloat(row.value)
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
}

// example of database
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

// example of database
function getEnergyIntensity(sqlFile) {

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

/* ################################################################################################ */
var sqlFile   = 'eem_1.sql'; 
console.log('Test sqlite3 \n=====================================\nFile: '+ sqlFile);
//getEnergyUse(sqlFile);
//getEnergyCost(sqlFile);









