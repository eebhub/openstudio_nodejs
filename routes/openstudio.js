var timestp = require("../lib/timestamp.js");
var fs = require("fs");

module.exports = {

    simulateOpenstudio: function(request, response){
      console.log(request.body);
      
      var random_number = request.body.random_number;
      // basic building and user's info
      var building_name = request.body.building_name;
      var weather_epw_location = request.body.weather_epw_location;
      var tightness = request.body.tightness;
      var activity_type = request.body.activity_type;
      
      var activity_type_specific = '';
      switch(activity_type) {
            case "SmallOffice":
            case "MediumOffice":
            case "LargeOffice":
                activity_type_specific = 'WholeBuilding'; 
                break;
            case "Warehouse":
                activity_type_specific = 'Office'; 
                break;
            case "Retail":
                activity_type_specific = 'Core'; 
                break;
      }

      // material and area    
      var number_of_floors = request.body.number_of_floors;
      var gross_floor_area = 1; //not use yet, area is re-define from the geometric below
      var roof_type = request.body.roof_type;
      var exterior_wall_type = request.body.exterior_wall_type;
      var footprint_shape = request.body.footprint_shape;
                 
      //lighting
      var room_depth = request.body.room_depth;
      var room_width = request.body.room_width;
      var room_height = request.body.room_height;
      var wall_thickness = request.body.wall_thickness;
      var floor_to_floor_height = request.body.floor_to_floor_height;
      var window_to_wall_ratio = request.body.window_to_wall_ratio/100;
      var building_height = floor_to_floor_height*number_of_floors;
      var window_head_height = request.body.window_head_height;
      var building_orientation = request.body.building_orientation;
      var overhang_depth = request.body.overhang_depth;
      var lighting_power_density = request.body.lighting_power_density;
      var illuminance = request.body.illuminance;
      var ceiling_reflectance = request.body.ceiling_reflectance;
      var wall_reflectance = request.body.wall_reflectance;
      var floor_reflectance = request.body.floor_reflectance;
      var window_transmittance = request.body.window_transmittance;
      var interior_shading_type = request.body.interior_shading_type;

      var timestamp = timestp.createTimestamp();
      var filename = (building_name.replace(/\s+/g, '-') || "NoName")+ timestamp;
      fs.mkdirSync('openstudio/outputs/eem/'+filename);
      
      var cmd = 'xvfb-run -a ruby run_eebhub.rb '+random_number+' EMPTY'+' "'+building_name+'"'+' '+weather_epw_location+' '+tightness+' '+activity_type+' '+activity_type_specific+' '+number_of_floors+' '+gross_floor_area+' "'+ roof_type+'" "'+exterior_wall_type + '" '+window_to_wall_ratio+' '+footprint_shape;
      
      
      // geometric info                 
    switch(footprint_shape) {
    case "Rectangle": 
        cmd = cmd + ' '+request.body.length+' '+request.body.width;
        break;
        
    case "H": 
        cmd = cmd+' '+request.body.length+' '+' '+request.body.left_width+ request.body.center_width+' '+request.body.right_width+' '+request.body.left_end_length+' '+request.body.right_end_length+' '+request.body.left_upper_end_offset+' '+request.body.right_upper_end_offset;
        break;
        
    case "L":  
        cmd = cmd+' '+request.body.length+' '+request.body.width+' '+request.body.end_1+' '+request.body.end_2;
        break;
        
    case "T": 
        cmd = cmd+' '+request.body.length+' '+request.body.width+' '+request.body.end_1+' '+request.body.end_2+' '+request.body.offset;
        break;
        
    case "U": 
        cmd = cmd+' '+request.body.length+' '+request.body.width_1+' '+request.body.width_2+' '+request.body.end_1+' '+request.body.end_2+' '+request.body.offset;
        break;
        
    case "Pie":
        cmd = cmd+' '+request.body.radius_a+' '+request.body.radius_b+' '+request.body.num_points+' '+request.body.degree;
        break;
    
    }
      
      //response.send('file created!');
      var exec = require('child_process').exec;
      console.log(cmd);
    //   exec('ssh bitnami@128.118.67.241 "'+cmd+'"', function(err, stdout, stderr){
    //   });
 
    }
    
};    