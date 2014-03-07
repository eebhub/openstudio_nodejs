// OPENSTUDIO-RUN.JS ----------------------------------------------------------------------------------------------------
// 1 - PARSE DATA from  buildingData.json
// 2 - REQUIRE OpenstudioModel.js file
// 3 - RUN OpenStudio & save osm, idf (show terminal output)
// 4 - RUN EnergyPlus & save sql, html (show terminal output)

//Nodejs File System
var fs = require("fs");
//Timestamp code
var timestp = require("./timestamp.js");

// 1 - PARSE DATA from buildingData.json ---------------------------------------------------------------------------------

//READ Building Input JSON
var data = JSON.parse(fs.readFileSync('buildingData.json', 'utf8')); 

//USE Building Input JSON Data Object
console.log("OpenStudio on Node.js starting up...");
console.log("INPUTS:");
console.log(data.building.building_info.building_name);
console.log(data.building.location.location_filename);
console.log(data.building.building_info.activity_type);
console.log(data.building.building_info.activity_type_specific);
console.log(data.building.architecture.number_of_floors+" floors");

//CREATE Unique Simulation Name & Folder
var buildingName = data.building.building_info.building_name.replace(/\s+/g, '') || "NoName";
var timestamp = timestp.createTimestamp();
var buildingNameTimestamp =  buildingName+timestamp;
fs.mkdirSync(buildingNameTimestamp, function(error) {if (error) throw error;}); //simulation_directory in openstudio-model.js

// 2 - REQUIRE OpenstudioModel.js file ---------------------------------------------------------------------------------

console.log("SETUP OPENSTUDIO MODEL:");
var OpenStudioModel = require("./openstudio-model.js").OpenStudioModel;

//Debugging Output Level (High = -3, Medium = -2, Low = -1)
openstudio.Logger.instance().standardOutLogger().setLogLevel(-3);

// Disable the gui (true, false, false) this makes the xvfb no longer necessary
var runmanager = new openstudio.runmanager.RunManager(true, false, false);
var co = runmanager.getConfigOptions();
co.fastFindEnergyPlus();
runmanager.setConfigOptions(co);

//Send (data = buildingData.json, runmanager = disable gui) to OpenStudioModel
var model = new OpenStudioModel(data, runmanager);

// 3 - RUN OpenStudio & save osm, idf -----------------------------------------------------------------------------------

console.log("CONVERT OPENSTUDIO TO ENERGYPLUS:");
model.save_openstudio_osm(buildingNameTimestamp, buildingNameTimestamp+"_input.osm");
model.translate_to_energyplus_and_save_idf(buildingNameTimestamp, buildingNameTimestamp+"_input.idf");

model.add_load_summary_report(buildingNameTimestamp+"/"+buildingNameTimestamp+"_input.idf");
model.convert_unit_to_ip(buildingNameTimestamp+"/"+buildingNameTimestamp+"_input.idf");

// 4 - RUN EnergyPlus & save sql, html -----------------------------------------------------------------------------------

console.log("RUN ENERGYPLUS:");
var job = model.run_energyplus_simulation(buildingNameTimestamp, buildingNameTimestamp+"_input.idf");

var treeerrors = job.treeErrors();

console.log("OUTPUT SUMMARY:");
console.log("Job Succeeded: " + treeerrors.succeeded());

var errors = treeerrors.errors();
var warnings = treeerrors.warnings();

for (var i = 0; i < errors.size(); ++i)
{
  console.log("Error: " + errors.get(i));
}

for (var i = 0; i < warnings.size(); ++i)
{
  console.log("Warning: " + warnings.get(i));
}