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
console.log(data.building.materials.roof_type);
console.log(data.building.materials.exterior_wall_type);
console.log(data.building.typical_room.window_to_wall_ratio);
console.log(data.building.architecture.footprint_shape);
console.log(data.building.architecture.building_length);
console.log(data.building.architecture.building_width);
//http://stackoverflow.com/questions/14028259/json-response-parsing-in-javascript-to-get-key-value-pair

// 2 - REQUIRE OpenstudioModel.js file ---------------------------------------------------------------------------------

