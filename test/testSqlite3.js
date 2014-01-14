#! /usr/bin/env node

// modules
var sqlite3 = require('sqlite3').verbose();

// example of database
function queryData () {

    var db = new sqlite3.Database('Output/example.sql');

    if (db) {
        console.log("\nconnected to database [SUCCESS]\n");
    } else {
        console.log("could not connect to database [FAILED]");
    }

    // query statement
    var stmt = 'select rowname, value, units from tabulardatawithstrings where tablename like "Site and Source Energy";';

    // print data to stdout
    db.each(stmt, function(err, row) {
       console.log(row.value);
    });

    db.close(); 
}

queryData();





