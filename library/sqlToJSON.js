//Dependencies
var sqlite3 = require('sqlite3').verbose();

//Export Function
module.exports.sqlToJSON = sqlToJSON;

function sqlToJSON(sqlFilePath, callback) {
    //Initialize Database access
    var db = new sqlite3.Database(sqlFilePath);
    //Construction Initial Building Data Object
    var building = {
        "general": {},
        "windowWallRatio": {},
        "skylightRoofRatio": {},
        "zoneSummary": {},
        "elecConsumption": {},
        "ngConsumtion": {},
        "energyIntensity": {
            "total": {},
            "site": {},
            "source": {}
        },
        "area": {},
        "tariffs": {},
        "comfortSetpointSummary":{},
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
        }
    };
    //SQL Commands for SQLite3 Database
    //Monthly SQL Commands
    var monthlyElSql = "Select Distinct * From TabularDataWithStrings Where ReportName Like 'END USE ENERGY CONSUMPTION ELECTRICITY MONTHLY'";
    var monthlyNGSql = "Select Distinct * From TabularDataWithStrings Where ReportName Like 'END USE ENERGY CONSUMPTION NATURAL GAS MONTHLY'";
    //Energy Intensity SQL
    var eISql = "Select Distinct * From TabularDataWithStrings Where ReportName Like 'AnnualBuildingUtilityPerformanceSummary'";
    var sourceSQL = "Select Distinct * From TabularDataWithStrings Where ReportName Like 'SourceEnergyEndUseComponentsSummary'";
    //Tariff SQL
    var tariffSQL = "Select Distinct * From TabularDataWithStrings Where ReportName Like 'Tariff Report' AND TableName Like 'Categories'";
    //General Data SQL
    var summarySQL = "Select Distinct * From TabularDataWithStrings Where ReportName Like 'InputVerificationandResultsSummary'";
    //SQL for Comfort and Setpoints
    var setpointSql = "Select Distinct * From TabularDataWithStrings Where ReportName Like 'AnnualBuildingUtilityPerformanceSummary' and TableName Like 'Comfort and Setpoint Not Met Summary'";
    //Object Constuctors
    function month(energyType, value, units) {
        this.energyType = energyType;
        if (isNaN(parseInt(value))) {
            this.value = value;
        } else {
            this.value = parseInt(value);
        };
        this.units = units;
    };

    function siteSource(type, value, units) {
        this.type = type;
        if (isNaN(parseInt(value))) {
            this.value = value;
        } else {
            this.value = parseInt(value);
        };
        this.units = units;
    };

    function tariffs(type, value) {
        this.type = type;
        if (isNaN(parseInt(value))) {
            this.value = value;
        } else {
            this.value = parseInt(value);
        };
        this.units = "$";
    };

    function generalData(value, units) {
        if (isNaN(parseInt(value))) {
            this.value = value;
        } else {
            this.value = parseInt(value);
        };

        this.units = units;
    }

    function wwRatioData(type, value, units) {
        this.type = type;
        if (isNaN(parseInt(value))) {
            this.value = value;
        } else {
            this.value = parseInt(value);
        };
        this.units = units;
    }

    function skyRatioData(value, units) {
        if (isNaN(parseInt(value))) {
            this.value = value;
        } else {
            this.value = parseInt(value);
        };
        this.units = units;
    }

    function zoneSumData(type, value, units) {
        this.type = type;
        if (isNaN(parseInt(value))) {
            this.value = value;
        } else {
            this.value = parseInt(value);
        };
        this.units = units;
    }

    function setpoint(value, units) {
        if (isNaN(parseInt(value))) {
            this.value = value;
        } else {
            this.value = parseInt(value);
        };
        this.units = units;
    }
    //DB Read for general data
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

    });
    //Monthly Consumption DB queries
    //Electricity
    db.all(monthlyElSql, function (err, rows) {
        rows.forEach(function (row) {
            row.ColumnName = row.ColumnName.substring(0, row.ColumnName.search(":"));

            if (building.elecConsumption[row.RowName]) {
                building.elecConsumption[row.RowName].push(new month(row.ColumnName, row.Value, row.Units));
            } else {
                building.elecConsumption[row.RowName] = [];
                building.elecConsumption[row.RowName].push(new month(row.ColumnName, row.Value, row.Units));
            }
        });
    });
    //Natural Gas
    db.all(monthlyNGSql, function (err, rows) {
        rows.forEach(function (row) {
            row.ColumnName = row.ColumnName.substring(0, row.ColumnName.search(":"));
            if (building.ngConsumtion[row.RowName]) {
                building.ngConsumtion[row.RowName].push(new month(row.ColumnName, row.Value, row.Units));
            } else {
                building.ngConsumtion[row.RowName] = [];
                building.ngConsumtion[row.RowName].push(new month(row.ColumnName, row.Value, row.Units));
            }
        });
    });

    //Energy Intensity DB Queries
    db.all(eISql, function (err, rows) {
        rows.forEach(function (row) {
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
    //Comfort and Setpoint DB read
    db.all(setpointSql, function (err, rows) {
        rows.forEach(function (row) {
            building.comfortSetpointSummary[row.RowName] = new setpoint(row.Value, row.Units);
        });
    });
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
        callback(building);
    });
    db.close();

};
