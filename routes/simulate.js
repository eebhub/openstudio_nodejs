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
    var buildingNameTimestamp =  buildingName+timestamp;
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
      "buildingLength": request.body.buildingLength*1,
      "buildingWidth": request.body.buildingWidth*1,
      "buildingHeight": request.body.floorToFloorHeight*request.body.numberOfFloors,
      "numberOfFloors": request.body.numberOfFloors*1,
      "floorToFloorHeight": request.body.floorToFloorHeight*1,
      "degreeToNorth": request.body.degreeToNorth*1,
      "plenumHeight": 0.0,
      "perimeterZoneDepth": 3.0,
      "windowToWallRatio": request.body.windowToWallRatio*1,
      "windowOffset": 1.0,
      "windowOffsetApplicationType": "Above Floor"
    },
    "mechanical": {
      "fanEfficiency": request.body.fanEfficiency*1,
      "boilerEfficiency": request.body.boilerEfficiency*1,
      "boilerFuelType": request.body.boilerFuelType,
      "coilCoolRatedHighSpeedCOP": request.body.coilCoolRatedHighSpeedCOP*1,
      "coilCoolRatedLowSpeedCOP": request.body.coilCoolRatedLowSpeedCOP*1,
      "economizerType": request.body.economizerType,
      "economizerDryBulbTempLimit": 30,
      "heatingSetpoint": request.body.heatingSetpoint*1,
      "coolingSetpoint": request.body.coolingSetpoint*1
    },
    "construction": {
      "constructionLibraryPath": "./library/defaultConstructionMaterials.osm"
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
    
    //ATTEMPT#1:  This new OpenStudio/EnergyPlus JavaScript instance computationally consumed all of app.js, so we moved to child_process.fork...
        //var OpenStudioRun = require("../library/openstudio-run.js").OpenStudioRun;
        //var run = new OpenStudioRun(buildingDataFileName); 
    
    //ATTEMPT#2:  Since Node is asynchronous and the OpenStudio/EnergyPlus fork takes 5+ minutes, the response.redirect happened before EnergyPlus was finished, so we blocked redirect with a procedural module...
        // var fork = require('child_process').fork;
        // var command = fork("./library/openstudio-run.js", [buildingDataFileName], {silent: true });

    //ATTEMPT#3:  Syncronously (or async.series): 1st Run EnergyPlus, 2nd response.redirect.  This also blocked client side progress bar, so we removed async module...
        // var async = require('async');
        // async.series([
        //     function(callback){ 
        //         var fork = require('child_process').fork;
        //         var command = fork("./library/openstudio-run.js", [buildingDataFileName], {silent: true }); //silent because http://stackoverflow.com/questions/22275556/node-js-forked-pipe
        //         command.stdout.pipe(process.stdout);
        //         command.stderr.pipe(process.stderr);
                
        //         //CREATE write file for stdout, LISTEN to stdout, WRITE to file
        //         var file = fs.createWriteStream(outputPath+'progress.txt');
        //         command.stdout.on('data', function(data) {file.write(data);});
        //         command.stderr.on('data', function(data) {file.write(data);});
        //         command.stdout.on('end', function(data) {file.end();});
        //         command.stderr.on('end', function(data) {file.end();});
                
        //         //when child process exits, check if there were any errors and close the writeable stream
        //         command.on('exit', function(code) {
        //             if (code !== 0) {console.log('Failed: ' + code);}
        //             callback();
        //         });
        //     },
        //     function(callback){ 
                
        //         //RENDER EnergyPlus Graphs & Files outputs.ejs
        //         response.redirect(outputPath+"1-EnergyPlus-0/eplustbl.htm"); //redirecting to output HTML until outputs.ejs ready
                
        //         callback();
        //     }
        // ]);
    
    //ATTEMPT#4:  Fork OpenStudio/EnergyPlus process to free up memory for app.js & redirect on CLIENT side javascript (when E+ stdout finishes).
    var fork = require('child_process').fork;
    var command = fork("./library/openstudio-run.js", [buildingDataFileName], {silent: true }); //silent because http://stackoverflow.com/questions/22275556/node-js-forked-pipe
    command.stdout.pipe(process.stdout);
    command.stderr.pipe(process.stderr);
    //CREATE write file for stdout, LISTEN to stdout, WRITE to file
    var file = fs.createWriteStream(outputPath+'progress.txt');
    command.stdout.on('data', function(data) {file.write(data);});
    command.stderr.on('data', function(data) {file.write(data);});
    command.stdout.on('end', function(data) {file.end();});
    command.stderr.on('end', function(data) {file.end();});
    
    // when child process exits, check if there were any errors and close the writeable stream
    command.on('exit', function(code) {
        if (code !== 0) {console.log('Failed: ' + code);}
    });
    
    //WRITE Output to buildingData.json / APPEND important energyplus output sql tables into original json
    var databasePath = outputPath+"1-EnergyPlus-0/eplusout.sql";
   
    //RENDER EnergyPlus Graphs & Files outputs.ejs
    //Logic now in loading() javascript function on client side

}//end openstudio
};//end exports
