#! /usr/bin/env node

// modules
var sqlite3 = require('sqlite3').verbose();

// example of database
function queryData (sqlFile, stmt) {

    var db = new sqlite3.Database(sqlFile);

    if (db) {
        console.log("\nconnected to database " + sqlFile + " [SUCCESS]");
    } else {
        console.log("\ncould not connect to database " + sqlFile + "[FAILED]");
    }

    // query statement
    //var stmt = 'select rowname, value, units from tabulardatawithstrings where tablename like "Site and Source Energy";';

    // print data to stdout
    db.each(stmt, function(err, row) {
       console.log(row.value);
    });

    db.close(); 
}

var sqlFile = 'Output/baseline.sql'; 
var statement = 'select rowname, value, units from tabulardatawithstrings where tablename like "Site and Source Energy";';

console.log('Test sqlite3 \n=====================================\nfile: '+ sqlFile + '\nstatement: ' + statement);
queryData(sqlFile, statement);





