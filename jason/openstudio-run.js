// OPENSTUDIO-RUN.JS ----------------------------------------------------------------------------------------------------
// 1 - PARSE DATA from  buildingData.json
// 2 - REQUIRE OpenstudioModel.js file
// 3 - RUN OpenStudio & save osm, idf (show terminal output)
// 4 - RUN EnergyPlus & save sql, html (show terminal output)

// 1 - PARSE DATA from buildingData.json ---------------------------------------------------------------------------------
//Nodejs File System
var fs = require("fs");

//READ Building Input JSON
var data = JSON.parse(fs.readFileSync('buildingData.json', 'utf8')); //buildingData.json should sit in the same folder directory as openstudio-run.js
//http://stackoverflow.com/questions/10011011/using-node-js-how-do-i-read-a-json-object-into-server-memory

//USE Building Input JSON Data Object (here for Jason, feel free to delete/re-use)
console.log(data._id);
console.log(data.username);
console.log(data.building.building_info.building_name);
console.log(data.building.building_info.weather_epw_location);
console.log(data.building.building_info.activity_type);
console.log(data.building.building_info.activity_type_specific);
console.log(data.building.architecture.number_of_floors);
console.log(data.building.architecture.gross_floor_area);
//http://stackoverflow.com/questions/14028259/json-response-parsing-in-javascript-to-get-key-value-pair

// 2 - REQUIRE OpenstudioModel.js file ---------------------------------------------------------------------------------
var OpenStudioModel = require("./openstudio-model.js").OpenStudioModel;

openstudio.Logger.instance().standardOutLogger().setLogLevel(-2);

// Disable the gui, this makes the xvfb no longer necessary
var runmanager = new openstudio.runmanager.RunManager(true, false, false);
var co = runmanager.getConfigOptions();
co.fastFindEnergyPlus();
runmanager.setConfigOptions(co);
var model = new OpenStudioModel(data, runmanager);

model.save_openstudio_osm("osm_dir", "test.osm");
model.translate_to_energyplus_and_save_idf("idf_dir", "test.idf");

model.add_load_summary_report("idf_dir/test.idf");
model.convert_unit_to_ip("idf_dir/test.idf");

var job = model.run_energyplus_simulation("idf_dir", "test.idf");

var treeerrors = job.treeErrors();

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




