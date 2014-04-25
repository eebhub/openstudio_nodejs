var sqlite3 = require('sqlite3').verbose();
var database = 'eem_1.sql';
var fs = require('fs');

var db = new sqlite3.Database(database);
var allSql = "Select Distinct * From TabularDataWithStrings";
var building = {};
function finalObj(type, value, units){
    this.type = type;
    this.value = value;
    this.units = units;
}
db.all(allSql, function (err, rows) {
    rows.forEach(function(row){
        if(building[row.ReportName]){
            if(building[row.ReportName][row.TableName]){
                if(building[row.ReportName][row.TableName][row.RowName]){
                    building[row.ReportName][row.TableName][row.RowName].push(new finalObj(row.ColumnName, parseInt(row.Value), row.Units));
                }else{
                    building[row.ReportName][row.TableName][row.RowName]=[];
                     building[row.ReportName][row.TableName][row.RowName].push(new finalObj(row.ColumnName, parseInt(row.Value), row.Units));
                }
            }else{
                building[row.ReportName][row.TableName] = [];
                building[row.ReportName][row.TableName][row.RowName] = [];
                building[row.ReportName][row.TableName][row.RowName].push(new finalObj(row.ColumnName, parseInt(row.Value), row.Units));
            }
        }else{
            building[row.ReportName] = {};
            building[row.ReportName][row.TableName] = [];
            building[row.ReportName][row.TableName][row.RowName] = [];
            building[row.ReportName][row.TableName].push(new finalObj(row.ColumnName, parseInt(row.Value), row.Units));
        }


    })
    //console.log(building['END USE ENERGY CONSUMPTION ELECTRICITY MONTHLY']['']);
    fs.writeFile('test.json', JSON.stringify(building, null, 4));
});

var testSQL = "Select * FROM SystemSizes";
db.all(testSQL, function(err, rows){
    console.log(rows);
});
