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
    
    
    //SAVE formatted json to outputPath with name buildingNameTimestamp_input.json
    
    
    //RUN openstudio-run.js & openstudio-model.js
    
    //APPEND important energyplus output sql tables into original json
    
    //RENDER EnergyPlus Graphs & Files outputs.ejs
    response.redirect(outputPath); //redirecting to folder until outputs.ejs ready

}//end openstudio
};//end exports


