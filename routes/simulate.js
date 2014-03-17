//DEPENDENCIES
var fs = require("fs");
//var openstudioRun = require("../library/openstudio-run.js");
//var openstudioModel = require("../library/openstudio-model.js");

//SIMULATE OPENSTUDIO
module.exports = {openstudio: function(request, response) {
    
    console.log('hello from simulate.js');
    console.log(request.body);
    
    response.redirect('energy-use');

}//end openstudio
};//end exports


