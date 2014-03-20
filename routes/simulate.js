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

    findStdOut = outputPath;		// set global variable from app.js to simulationID

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
        "city": "Philadelphia",
        "weather": "USA_IL_Chicago-OHare.Intl.AP.725300_TMY3",
        "climateZone": "ClimateZone 1-8",
        "strictDesignDay": "no"
    },
    "buildingInfo": {
      "buildingName": "JASON",
      "activityType": "SmallOffice",
      "activityTypeSecondary": "WholeBuilding",
      "yearCompleted": "1911",
      "units": "si",
      "ASHRAEStandard": "ASHRAE_90.1-2004"
    },
    "architecture": {
      "footprintShape": "Rectangle",
      "buildingLength": 100,
      "buildingWidth": 50,
      "buildingHeight": 30,
      "numberOfFloors": 3,
      "floorToFloorHeight": 3.0,
      "degreeToNorth": 15,
      "plenumHeight": 1.0,
      "perimeterZoneDepth": 3.0,
      "windowToWallRatio": 0.4,
      "windowOffset": 1.0,
      "windowOffsetApplicationType": "Above Floor"
    },
    "mechanical": {
      "fanEfficiency": 0.5,
      "boilerEfficiency": 0.66,
      "boilerFuelType": "NaturalGas",
      "coilCoolRatedHighSpeedCOP": 3.5,
      "coilCoolRatedLowSpeedCOP": 4.5,
      "economizerType": "No Economizer",
      "economizerDryBulbTempLimit": 30,
      "heatingSetpoint": 20,
      "coolingSetpoint": 24
    },
    "construction": {
      "constructionLibraryPath": "/home/joshuakuiros/Desktop/openstudio_nodjs/library/defaultConstructionMaterials.osm"
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
