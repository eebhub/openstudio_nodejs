var sqlite3 = require('sqlite3').verbose();
var fs = require('fs');

var databasePath = './eem_1.sql';

var db = new sqlite3.Database(databasePath);
var building = {
    "jan": [],
    "feb": [],
    "mar": [],
    "apr": [],
    "may": [],
    "jun": [],
    "jul": [],
    "aug": [],
    "sep": [],
    "oct": [],
    "nov": [],
    "dec": [],
    "ann": [],
    "min": [],
    "max": []
}

var sql = "Select Distinct * From TabularDataWithStrings Where ReportName Like 'END USE ENERGY CONSUMPTION ELECTRICITY MONTHLY'"

    function month(energyType, value, units) {
        this.energyType = energyType;
        this.value = value;
        this.units = units;
    };

db.all(sql, function (err, rows) {
    rows.forEach(function (row) {
        delete row.ReportName;
        delete row.TableName;
        delete row.ReportForString;
        delete row.RowId;
        var value = parseInt(row.Value);
        row.Value = value;
        row.ColumnName = row.ColumnName.substring(0, row.ColumnName.search(":"));
        var curMonth = row.RowName.substring(0, 3).toLowerCase();
        if (row.Value !== 0) {
            if (curMonth) {
                building[curMonth].push(new month(row.ColumnName, row.Value, row.Units));
            }
        };



    });
    console.log(building);
    fs.writeFile('output.json', JSON.stringify(building), function (err) {
        if (err) throw err;
        console.log('It\'s saved!');
    });
});
