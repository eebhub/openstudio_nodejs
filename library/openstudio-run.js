// OPENSTUDIO-RUN.JS ----------------------------------------------------------------------------------------------------
// 1 - PARSE DATA from  buildingData.json
// 2 - REQUIRE OpenstudioModel.js file
// 3 - RUN OpenStudio & save osm, idf (show terminal output)
// 4 - RUN EnergyPlus & save sql, html (show terminal output)

//Nodejs File System
var fs = require("fs");

// 1 - PARSE DATA from buildingData.json ---------------------------------------------------------------------------------

//READ Building Input JSON
var building = JSON.parse(fs.readFileSync('buildingData3.json', 'utf8')); 

//USE Building Input JSON Data Object
console.log("OpenStudio-Run on Node.js starting up...");
console.log("INPUTS:");
console.log("Name: "+ building.buildingInfo.buildingName);
console.log("Weather: "+ building.site.weather);
console.log("Function: "+ building.buildingInfo.activityType);
console.log("Floors: "+ building.architecture.numberOfFloors+" floors");
console.log("Window to Wall Ratio: "+ building.architecture.windowToWallRatio);

//CREATE Unique Simulation Name & Folder
var simulationID = building.simulationID;
var outputPath = building.paths.outputPath + simulationID;

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
var model = new OpenStudioModel(building, runmanager);
model.add_geometry(building.architecture);
model.add_windows(building.architecture);
model.add_hvac(building.mechanical);
model.add_thermostats(building.mechanical);
model.add_constructions(building.architecture, building.construction);
model.add_space_type(building.buildingInfo, building.site, building.paths);
model.add_densities();
model.add_design_days(building.site, openstudio.toString(model.runManager.getConfigOptions().getDefaultEPWLocation()));

// 3 - RUN OpenStudio & save osm, idf -----------------------------------------------------------------------------------

console.log("CONVERT OPENSTUDIO TO ENERGYPLUS:");
model.save_openstudio_osm(outputPath, simulationID +"_input.osm");
model.translate_to_energyplus_and_save_idf(outputPath, simulationID +"_input.idf");

model.add_load_summary_report(outputPath + simulationID +"_input.idf");
model.convert_unit_to_ip(outputPath + simulationID +"_input.idf");

// 4 - RUN EnergyPlus & save sql, html -----------------------------------------------------------------------------------

console.log("RUN ENERGYPLUS:");
var job = model.run_energyplus_simulation(outputPath, simulationID +"_input.idf");

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