//DEPENDENCIES
var fs = require("fs"); //Nodejs File System
var timestp = require("../library/timestamp.js"); //Timestamp code

//SIMULATE OPENSTUDIO
module.exports = {openstudio: function(request, response) {

    console.log('OpenStudio on Node.js Express starting up...');
    console.log('FORM DATA:');
    console.log(request.body);

    //CREATE unique simulation Name & Timestamp ID
    console.log("Creating unique Building Name & Folder...");
    var buildingName = request.body.buildingName.replace(/\s+/g, '') || "NoName";
    var timestamp = timestp.createTimestamp();
    var buildingNameTimestamp =  "TEST_"+buildingName+timestamp;
    var simulationID = buildingNameTimestamp;

    //CREATE unique simulation Folder
    var simulationsPath = "../simulations/";  //CHANGE for your local setup: update bitnami to your username, make simulations directory
    var outputPath = simulationsPath + simulationID +"/";
    fs.mkdirSync(outputPath, function(error) {if (error) throw error;});

    //FORMAT request.body json to match buildingData2.json
    console.log("Creating building json document...");
    var buildingDataFileName = outputPath + simulationID +'_input.json';
    console.log("BUILDING DATA:");
    var buildingData =
    {
    "__v": 0,
    "_id": "52a0e3ab5c9ac86f54000002",
    "username": "eebhub",
    "simulationID": simulationID,
    "site":{
        "city": request.body.weather,
        "weather": request.body.weather,
        "climateZone": "ClimateZone 1-8",
        "strictDesignDay": "no"
    },
    "buildingInfo": {
      "buildingName": request.body.buildingName,
      "activityType": request.body.activityType,
      "activityTypeSecondary": "WholeBuilding",
      "yearCompleted": request.body.yearCompleted,
      "units": "si",
      "ASHRAEStandard": "ASHRAE_90.1-2004"
    },
    "architecture": {
      "footprintShape": request.body.footprintShape,
      "buildingLength": request.body.buildingLength,
      "buildingWidth": request.body.buildingWidth,
      "buildingHeight": request.body.floorToFloorHeight*request.body.numberOfFloors,
      "numberOfFloors": request.body.numberOfFloors,
      "floorToFloorHeight": request.body.floorToFloorHeight,
      "degreeToNorth": request.body.degreeToNorth,
      "plenumHeight": request.body.plenumHeight,
      "perimeterZoneDepth": 3.0,
      "windowToWallRatio": request.body.windowToWallRatio,
      "windowOffset": 1.0,
      "windowOffsetApplicationType": "Above Floor"
    },
    "mechanical": {
      "fanEfficiency": request.body.fanEfficiency,
      "boilerEfficiency": request.body.boilerEfficiency,
      "boilerFuelType": request.body.boilerFuelType,
      "coilCoolRatedHighSpeedCOP": request.body.coilCoolRatedHighSpeedCOP,
      "coilCoolRatedLowSpeedCOP": request.body.coilCoolRatedLowSpeedCOP,
      "economizerType": request.body.economizerType,
      "economizerDryBulbTempLimit": 30,
      "heatingSetpoint": request.body.heatingSetpoint,
      "coolingSetpoint": request.body.coolingSetpoint
    },
    "construction": {
      "constructionLibraryPath": "defaultConstructionMaterials.osm"
    },
    "schedules": {
        "occupancy":{
          "default":{
            "weekday": [0,0,0,0,0,0,0.1,0.2,0.95,0.95,0.9,0.95,0.5,0.95,0.95,0.95,0.95,0.3,0.1,0.1,0.05,0.05,0.05,0.05],
            "saturday": [0,0,0,0,0,0,0.1,0.1,0.3,0.3,0.3,0.3,0.1,0.1,0.1,0.1,0.1,0,0,0,0,0,0,0],
            "sunday": [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]
            },
          "priority":[
            {
                "startDay": "06/01/2013",
                "endDay": "06/31/2013",
                "weekday": [0,0,0,0,0,0,0.1,0.2,0.95,0.95,0.9,0.95,0.5,0.95,0.95,0.95,0.95,0.5,0.5,0.3,0.1,0.05,0.05,0.05],
                "saturday": [0,0,0,0,0,0,0.1,0.1,0.5,0.5,0.5,0.5,0.3,0.3,0.3,0.3,0.3,0,0,0,0,0,0,0],
                "sunday": [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]
            }
          ]
        }
    },
    "paths": {
      "buildingComponentLibraryPath": "/home/bitnami/bcl",
      "simulationsPath": simulationsPath,
      "outputPath": outputPath
    }

    };

    console.log(buildingData);

    //SAVE formatted json to outputPath with name buildingNameTimestamp_input.json
    var fileString = JSON.stringify(buildingData, null, 4);
    fs.writeFileSync(buildingDataFileName, fileString);
    console.log('Input file saved!');

    //RUN openstudio-run.js & openstudio-model.js
    var OpenStudioRun = require("../library/openstudio-run.js").OpenStudioRun;
    var run = new OpenStudioRun(buildingDataFileName);

    //APPEND important energyplus output sql tables into original json

    //WRITE Output to buildingData.json

    //RENDER EnergyPlus Graphs & Files outputs.ejs
    response.redirect(outputPath); //redirecting to folder until outputs.ejs ready

}//end openstudio
};//end exports
