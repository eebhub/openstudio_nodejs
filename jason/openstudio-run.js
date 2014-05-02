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
console.log("Output path = "+data.building.location.simulationsPath);

//CREATE Unique Simulation Name & Folder
var buildingName = data.building.building_info.building_name.replace(/\s+/g, '') || "NoName";
var timestamp = timestp.createTimestamp();
var buildingNameTimestamp =  buildingName+timestamp;
var outputPath = data.building.location.simulationsPath + buildingNameTimestamp;
fs.mkdirSync(outputPath, function(error) {if (error) throw error;}); //simulation_directory in openstudio-model.js

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

// 3 - RUN OpenStudio & save osm -----------------------------------------------------------------------------------

console.log("CONVERT OPENSTUDIO TO ENERGYPLUS:");
model.save_openstudio_osm(outputPath, buildingNameTimestamp+"_input.osm");

// these are no longer necessary, see notes in the "run_energyplus_simulation" code

//model.translate_to_energyplus_and_save_idf(outputPath, buildingNameTimestamp+"_input.idf"); "Save IDF" now in File [openstudio-model.js] Function [run_energyplus_simulation] Line [467]: workflow.addWorkflow(new openstudio.runmanager.Workflow("ModelToIdf->EnergyPlusPreProcess->EnergyPlus")); // ModelToIdf job enables AllSummary reports including ZoneComponentLoad
//model.add_load_summary_report(outputPath+buildingNameTimestamp+"_input.idf"); "Add Load Summary" now in File [openstudio-model.js] Function [run_energyplus_simulation] Line [467]: workflow.addWorkflow(new openstudio.runmanager.Workflow("ModelToIdf->EnergyPlusPreProcess->EnergyPlus")); // ModelToIdf job enables AllSummary reports including ZoneComponentLoad
//model.convert_unit_to_ip(outputPath+buildingNameTimestamp+"_input.idf"); "Convert Units to IP / English" now in File [openstudio-model.js] Function [run_energyplus_simulation] Line [471]: workflow.addParam(new openstudio.runmanager.JobParam("IPTabularOutput")); //Tell the translator to use IP in HTML

// 4 - RUN EnergyPlus & save sql, html -----------------------------------------------------------------------------------

console.log("RUN ENERGYPLUS:");
var job = model.run_energyplus_simulation(outputPath, buildingNameTimestamp+"_input.osm");

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

// use this to find whatever output files that were generated that you want
// "getLastByFilename" also exists, as well as "getAllBy*" versions if you are
// looking for a set of files that have the same extension, etc.
var sqlfile = job.treeOutputFiles().getLastByExtension("sql");
console.log("SQL ouput file created at: " + openstudio.toString(sqlfile.fullPath));

