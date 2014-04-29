var sqlite3 = require('sqlite3').verbose();
var fs = require('fs');

var databasePath = './eem_1.sql';

var db = new sqlite3.Database(databasePath);
var building = {
    "elecConsumption": {},
    "ngConsumtion": {},
    "energyIntensity": {
        "total": {},
        "site": {},
        "source": {}
    },
    "area": {},
    "tariffs": {},
    "siteToSourceConversion": {
        "Electricity": 3.167,
        "Natural Gas": 1.084,
        "District Cooling": 1.056,
        "District Heating": 3.613,
        "Steam": 0.300,
        "Gasoline": 1.050,
        "Diesel": 1.050,
        "Coal": 1.050,
        "Fuel Oil #1": 1.050,
        "Fuel Oil #2": 1.050,
        "Propane": 1.050,
        "Other Fuel 1": 1.000,
        "Other Fuel 2": 1.000
    },
    "general": {},
    "windowWallRatio": {},
    "skylightRoofRatio": {},
    "zoneSummary": {},
    "comfortSetpointSummary":{}
}
//Monthly SQL Commands
var monthlyElSql = "Select Distinct * From TabularDataWithStrings Where ReportName Like 'END USE ENERGY CONSUMPTION ELECTRICITY MONTHLY'";
var monthlyNGSql = "Select Distinct * From TabularDataWithStrings Where ReportName Like 'END USE ENERGY CONSUMPTION NATURAL GAS MONTHLY'";
//Object Constuctors
function month(energyType, value, units) {
    if (isNaN(value)) {
        this.value = value;
    } else {
        this.value = parseInt(value);
    };
    this.energyType = energyType;

    this.units = units;
};

function siteSource(type, value, units) {
    if (isNaN(value)) {
        this.value = value;
    } else {
        this.value = parseInt(value);
    };
    this.type = type;

    this.units = units;
}

function tariffs(type, value) {
    if (isNaN(value)) {
        this.value = value;
    } else {
        this.value = parseInt(value);
    };
    this.type = type;
    this.units = "$";
}
function setpoint(value, units) {
    if (isNaN(value)) {
        this.value = value;
    } else {
        this.value = parseInt(value);
    };
    this.units = units;
}


//Monthly DB queries
db.all(monthlyElSql, function (err, rows) {
    rows.forEach(function (row) {
        row.ColumnName = row.ColumnName.substring(0, row.ColumnName.search(":"));
        var curMonth = row.RowName.substring(0, 3).toLowerCase();
        if (building.elecConsumption[curMonth]) {
            building.elecConsumption[curMonth].push(new month(row.ColumnName, row.Value, row.Units));
        } else {
            building.elecConsumption[curMonth] = [];
            building.elecConsumption[curMonth].push(new month(row.ColumnName, row.Value, row.Units));
        }
    });
});

db.all(monthlyNGSql, function (err, rows) {
    rows.forEach(function (row) {
        row.ColumnName = row.ColumnName.substring(0, row.ColumnName.search(":"));
        var curMonth = row.RowName.substring(0, 3).toLowerCase();
        if (building.ngConsumtion[curMonth]) {
            building.ngConsumtion[curMonth].push(new month(row.ColumnName, row.Value, row.Units));
        } else {
            building.ngConsumtion[curMonth] = [];
            building.ngConsumtion[curMonth].push(new month(row.ColumnName, row.Value, row.Units));
        }
    });
});

//Energy Intensity SQL
var eISql = "Select Distinct * From TabularDataWithStrings Where ReportName Like 'AnnualBuildingUtilityPerformanceSummary'";
var sourceSQL = "Select Distinct * From TabularDataWithStrings Where ReportName Like 'SourceEnergyEndUseComponentsSummary'";
//Energy Intensity DB Queries
db.all(eISql, function (err, rows) {
    rows.forEach(function (row) {
        delete row.ReportName;
        delete row.ReportForString;
        delete row.RowId;
        if (row.TableName == "Site and Source Energy") {
            if (building.energyIntensity.total[row.RowName]) {
                building.energyIntensity.total[row.RowName].push(new siteSource(row.ColumnName, row.Value, row.Units));
            } else {
                building.energyIntensity.total[row.RowName] = [];
                building.energyIntensity.total[row.RowName].push(new siteSource(row.ColumnName, row.Value, row.Units));
            }
        };
        if (row.TableName == "End Uses") {

            if (building.energyIntensity.site[row.RowName]) {
                row.Value = parseInt(row.Value);

                building.energyIntensity.site[row.RowName].push(new siteSource(row.ColumnName, row.Value, row.Units));
            } else {
                building.energyIntensity.site[row.RowName] = [];
                building.energyIntensity.site[row.RowName].push(new siteSource(row.ColumnName, row.Value, row.Units));
            }

        };
        if (row.TableName == "Building Area") {
            building.area[row.RowName] = new siteSource(row.ColumnName, row.Value, row.Units);
        };
    });
});
//Source DB read
db.all(sourceSQL, function (err, rows) {
    rows.forEach(function (row) {
        if (building.energyIntensity.source[row.RowName]) {
            building.energyIntensity.source[row.RowName].push(new siteSource(row.ColumnName, row.Value, row.Units));
        } else {
            building.energyIntensity.source[row.RowName] = [];
            building.energyIntensity.source[row.RowName].push(new siteSource(row.ColumnName, row.Value, row.Units));
        }
    });
});

//Tariff SQL
var tariffSQL = "Select Distinct * From TabularDataWithStrings Where ReportName Like 'Tariff Report' AND TableName Like 'Categories'";
//Tariff DB read
db.all(tariffSQL, function (err, rows) {
    rows.forEach(function (row) {
        row.RowName = row.RowName.substring(0, row.RowName.search(" "));
        if (building.tariffs[row.RowName]) {
            building.tariffs[row.RowName].push(new tariffs(row.ColumnName, row.Value));
        } else {
            building.tariffs[row.RowName] = [];
            building.tariffs[row.RowName].push(new tariffs(row.ColumnName, row.Value));
        }
    })

});

function generalData(value, units) {
    if (isNaN(value)) {
        this.value = value;
    } else {
        this.value = parseInt(value);
    };

    this.units = units;
}

function wwRatioData(type, value, units) {
    if (isNaN(value)) {
        this.value = value;
    } else {
        this.value = parseInt(value);
    };
    this.type = type;
    this.value = value;
    this.units = units;
}

function skyRatioData(value, units) {
    if (isNaN(value)) {
        this.value = value;
    } else {
        this.value = parseInt(value);
    };
    this.units = units;
}

function zoneSumData(type, value, units) {

    if (isNaN(value)) {
        this.value = value;
    } else {
        this.value = parseInt(value);
    };
    this.type = type;
    this.units = units;
}
var summarySQL = "Select Distinct * From TabularDataWithStrings Where ReportName Like 'InputVerificationandResultsSummary'";
db.all(summarySQL, function (err, rows) {
    rows.forEach(function (row) {
        if (row.TableName == "General") {
            building.general[row.RowName] = new generalData(row.Value, row.Units);
        }
        if (row.TableName == "Window-Wall Ratio") {
            if (building.windowWallRatio[row.RowName]) {
                building.windowWallRatio[row.RowName].push(new wwRatioData(row.ColumnName, row.Value, row.Units));
            } else {
                building.windowWallRatio[row.RowName] = [];
                building.windowWallRatio[row.RowName].push(new wwRatioData(row.ColumnName, row.Value, row.Units));
            };

        }
        if (row.TableName == "Skylight-Roof Ratio") {
            building.skylightRoofRatio[row.RowName] = new skyRatioData(row.Value, row.Units);
        }
        if (row.TableName == "Zone Summary") {
            if (building.zoneSummary[row.RowName]) {
                building.zoneSummary[row.RowName].push(new zoneSumData(row.ColumnName, row.Value, row.Units));
            } else {
                building.zoneSummary[row.RowName] = [];
                building.zoneSummary[row.RowName].push(new zoneSumData(row.ColumnName, row.Value, row.Units));
            };

        }
    });
    //console.log(building.zoneSummary);
    //fs.writeFileSync('buildingOutput.json', JSON.stringify(building, 4,4));
});
var setpointSql = "Select Distinct * From TabularDataWithStrings Where ReportName Like 'AnnualBuildingUtilityPerformanceSummary' and TableName Like 'Comfort and Setpoint Not Met Summary'";
db.all(setpointSql, function(err, rows){
    rows.forEach(function(row){
        building.comfortSetpointSummary[row.RowName] = new setpoint(row.ColumnName, row.Value, row.Units);
    });
});

