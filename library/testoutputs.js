var sqlite3 = require('sqlite3').verbose();
var sqlToJSON = require('./sqlToJSON.js').sqlToJSON;


sqlToJSON('../test/eem_1.sql', function(buildingData){
    console.log(buildingData.comfortSetpointSummary);
});
