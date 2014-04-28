var sqlite3 = require('sqlite3').verbose();
var number_format = require('./number_format.js');

module.exports = {
    /*
     *  get an array of Object(curRowname, curColumnName, value),
     *  return an array of object {curRowname: , curColumnName: , value: }
     */
    getValues: function(reportName, reportForString, tableName, units, db_path, fn) {
        var db = new sqlite3.Database(db_path);

        var sql = 'Select Distinct * From TabularDataWithStrings Where ReportName Like "' + reportName + '" And ReportForString Like "' + reportForString + '" and TableName = "' + tableName + '" and Units Like "' + units + '"';
        var results = [];

        db.all(sql, function(err, rows) {
            rows.forEach(function(row) {
                var curColumnName = row.ColumnName;
                var curRowName = row.RowName;
                var result = new Object();
                result.curRowName = curRowName;
                result.curColumnName = curColumnName;
                result.value = number_format.number_format(row.Value, 1, '.', '');

                results.push(result);
            });
            //console.log(results);
            fn(results);
            //return may not work due to the asynchronous
        });
        db.close;

    },

    /*
     *  Get Report For String For Zones Info Only
     *  Return an array of ReportForString attribute values
     */
    getReportForStrings: function(reportName, db_path, fn) {
        var db = new sqlite3.Database(db_path);
        var sql = 'Select Distinct "ReportForString" From TabularDataWithStrings where ReportName = "' + reportName + '"';
        var results = [];
        db.all(sql, function(err, rows) {
            rows.forEach(function(row) {
                var ReportForString = row.ReportForString;
                results.push(ReportForString);
            });
            //console.log(results);
            fn(results);
        });
        db.close;
    },


    /*
     *	Get Monthly Value without Minimum of Months, Annual Sum or Average, and Maximum of Months
     */
    getValuesByMonthly: function(reportName, reportForString, tableName, units, db_path, fn) {
        var db = new sqlite3.Database(db_path);
        var sql = 'Select Distinct * From TabularDataWithStrings Where ReportName Like "' + reportName + '" And ReportForString Like "' + reportForString + '" and TableName = "' + tableName + '" and Units Like "' + units + '"';

        var results = [];

        db.all(sql, function(err, rows) {
            rows.forEach(function(row) {
                var curColumnName = row.ColumnName;
                var curRowName = row.RowName;
                var result = new Object();
                result.curRowName = curRowName;
                result.curColumnName = curColumnName;
                if (curColumnName == '' | curRowName == '' | curRowName == 'Minimum of Months' | curRowName == 'Annual Sum or Average' | curRowName == 'Maximum of Months') {}
                else {
                    result.value = number_format.number_format(row.Value, 1, '.', '');
                    results.push(result);
                }

            });
            //console.log(results);
            fn(results);
        });
        db.close;
    },




}
