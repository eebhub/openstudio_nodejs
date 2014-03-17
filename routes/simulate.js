//DEPENDENCIES
var fs = require("fs"); //Nodejs File System
var timestp = require("../library/timestamp.js"); //Timestamp code
//var openstudioRun = require("../library/openstudio-run.js");
//var openstudioModel = require("../library/openstudio-model.js");

//SIMULATE OPENSTUDIO
module.exports = {openstudio: function(request, response) {
    
    console.log('hello from simulate.js');
    //console.log(request.body);
    
    var buildingData = 
    {
    "__v": 0,
    "_id": "52a0e3ab5c9ac86f54000002",
    "username": "eebhub",
    "site":{
        "city": "Philadelphia",
        "weather": request.body.weather_epw_location,
        "climateZone": "ClimateZone 1-8",
        "strictDesignDay": "no"
    },
    "buildingInfo": {
      "buildingName": request.body.building_name,
      "activityType": request.body.activity_type,
      "activityTypeSecondary": "WholeBuilding",
      "yearCompleted": request.body.year_completed,
      "units": "si",
      "ASHRAEStandard": "ASHRAE_90.1-2004"
    },
    "architecture": {
      "footprintShape": "Rectangle",
      "buildingLength": request.body.length,
      "buildingWidth": 50,
      "buildingHeight": 30,
      "numberOfFloors": request.body.number_of_floors,
      "floorToFloorHeight": request.body.floor_to_floor_height[0],
      "degreeToNorth": 15,
      "plenumHeight": request.body.floor_to_floor_height[1],
      "perimeterZoneDepth": 3.0,
      "windowToWallRatio": request.body.window_to_wall_ratio,
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
    
    var fileString = JSON.stringify(buildingData, null, 4);
    
    fs.writeFile('test/Input/test.json', fileString, function (err) {
        if (err) throw err;
        console.log('File is saved!');
    });
    
    
    //CREATE unique simulation Name & Timestamp
    var buildingName = request.body.buildingName.replace(/\s+/g, '') || "NoName";
    console.log(buildingName);
    var timestamp = timestp.createTimestamp();
    var buildingNameTimestamp =  "TEST_"+buildingName+timestamp;
    
    //CREATE unique simulation Folder
    var outputPath = "../simulations/" + buildingNameTimestamp;
    fs.mkdirSync(outputPath, function(error) {if (error) throw error;}); //simulation_directory in openstudio-model.js

    //FORMAT request.body json to match buildingData2.json 
    
    
    //SAVE formatted json to outputPath with name buildingNameTimestamp_input.json
    
    
    //RUN openstudio-run.js & openstudio-model.js
    
    //APPEND important energyplus output sql tables into original json
    
    //RENDER EnergyPlus Graphs & Files outputs.ejs
    response.redirect(outputPath); //redirecting to folder until outputs.ejs ready

}//end openstudio
};//end exports


