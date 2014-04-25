var sqlite3 = require('sqlite3').verbose();
var database = 'eem_1.sql';
var fs = require('fs');

var db = new sqlite3.Database(database);
var tableSQL = "SELECT name FROM sqlite_master WHERE type='table'";

var data = new Object();
function dataObj(type, value, units){
    this.type = type;
    this.value = value;
    this.units = units;
};

db.all("SELECT * FROM Simulations", function (err, rows) {
        data.simulations = rows;
    console.log(data);
    });

db.all("SELECT * FROM TabularDataWithStrings", function (err, rows) {
        rows.forEach(function(row){

        });
    });
