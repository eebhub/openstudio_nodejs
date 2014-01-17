var sqlite3 = require('sqlite3').verbose();
var number_format = require('./number_format.js'); 

module.exports = {
        /*
    	 *  get an array of Object(curRowname, curColumnName, value), 
    	 *  return may not work due to the asynchronous!!
    	 */	
    	getValues: function(reportName, reportForString, tableName, units, db_path){
	        var db = new sqlite3.Database(db_path);
	        
	        var sql = 'Select Distinct * From TabularDataWithStrings Where ReportName Like "' + reportName + '" And ReportForString Like "'+ reportForString + '" and TableName = "'+tableName + '" and Units Like "'+ units + '"';	
            var results = [];

            db.all(sql, function(err, rows) {
                rows.forEach(function (row) {
                var curColumnName = row.ColumnName;
                var curRowName = row.RowName;
                var result = new Object();
                result.curRowName = curRowName;
                result.curColumnName = curColumnName; 
                result.value = number_format.number_format(row.Value, 1, '.', '');

                    results.push(result);
                });
                console.log(results);
                db.close;
               //return may not work due to the asynchronous
            });
              
        
	    },
    
        
    
    }