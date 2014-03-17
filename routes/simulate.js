//DEPENDENCIES
var fs = require("fs"); //Nodejs File System
var timestp = require("../library/timestamp.js"); //Timestamp code
//var openstudioRun = require("../library/openstudio-run.js");
//var openstudioModel = require("../library/openstudio-model.js");

//SIMULATE OPENSTUDIO
module.exports = {openstudio: function(request, response) {
    
    console.log('hello from simulate.js');
    console.log(request.body);
    
    //CREATE unique simulation Name & Timestamp
    var buildingName = request.body.buildingName.replace(/\s+/g, '') || "NoName";
    console.log(buildingName);
    var timestamp = timestp.createTimestamp();
    var buildingNameTimestamp =  "TEST_"+buildingName+timestamp;
    
    //CREATE unique simulation Folder
    var outputPath = "../simulations/" + buildingNameTimestamp;
    fs.mkdirSync(outputPath, function(error) {if (error) throw error;});

     //FORMAT request.body json to match buildingData2.json 
    var buildingData = 
    {
    "__v": 0,
    "_id": "52a0e3ab5c9ac86f54000002",
    "username": "eebhub",
    "site":{
        "city": "Philadelphia",
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
      "footprintShape": "Rectangle",
      "buildingLength": request.body.buildingLength,
      "buildingWidth": request.body.buildingWidth,
      "buildingHeight": 30,
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
      "constructionLibraryPath": "VirtualPULSE_default_constructions.osm"
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
      "outputPath": ""
    }

    };

    console.log(buildingData);
    
    //SAVE formatted json to outputPath with name buildingNameTimestamp_input.json
    
    var fileString = JSON.stringify(buildingData, null, 4);
    
    fs.writeFileSync(outputPath+'/'+buildingNameTimestamp+'_input.json', fileString);

    console.log('Input file saved!');    
    
    //RUN openstudio-run.js & openstudio-model.js
    
    //APPEND important energyplus output sql tables into original json
    
    //RENDER EnergyPlus Graphs & Files outputs.ejs
    response.redirect(outputPath); //redirecting to folder until outputs.ejs ready

}//end openstudio
};//end exports


