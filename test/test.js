var sqlite3 = require('sqlite3').verbose();
var database = 'Output/baseline.sql';
var fs = require('fs');
var number_format = require('../library/number_format.js');


//Values by Month
function getValuesByMonth(reportName, reportForString, tableName, units, db_path, fn) {
    var db = new sqlite3.Database(db_path);
    var sql = 'Select Distinct * From TabularDataWithStrings Where ReportName Like "' + reportName + '" And ReportForString Like "' + reportForString + '" and TableName = "' + tableName + '" and Units Like "' + units + '"';
    var results = [];

    db.all(sql, function(err, rows) {
           rows.forEach(function(curRow){
              results.push(curRow);
           });
            //console.log(results);
            fn(results);
            //return may not work due to the asynchronous
        });
}

function getValues(reportName, reportForString, tableName, units, db_path, fn) {
        var db = new sqlite3.Database(db_path);

        var sql = 'Select Distinct * From TabularDataWithStrings Where ReportName Like "' + reportName + '" And ReportForString Like "' + reportForString + '" and TableName = "' + tableName + '" and Units Like "' + units + '"';
        var results = [];

        db.all(sql, function(err, rows) {
            rows.forEach(function(row) {
                results.push(row);
            });
            //console.log(results);
            fn(results);
            //return may not work due to the asynchronous
        });
        db.close;

    }
//Energy-Use
getValuesByMonth('END USE ENERGY CONSUMPTION ELECTRICITY MONTHLY', 'Meter', '', '%', database, function(results){
    console.log(results);
});
//Energy-Cost
getValues('Tariff Report', 'BLDG101_ELECTRIC_RATE', 'Categories', '%',database, function(results){
    console.log(results);
});
//Energy-Intensity
getValues('AnnualBuildingUtilityPerformanceSummary', 'Entire Facility', 'Site and Source Energy', '%', database, function(results){
    console.log(results);
});
