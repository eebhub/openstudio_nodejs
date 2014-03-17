//FILLS IN FORMS WITH BUILDING 101 DATA

//HTML
//<script src="js/building101Data.js"></script>
//<form action="action" method="post" name="platformForm">
//<button type="button"  onclick="building101Data()"> + Building 101</button>

//UNITS
//https://docs.google.com/spreadsheet/ccc?key=0AmpmAq6B1uv_dGNka3Q1LWVzaW5ObnNtbW9ZRkUwQ2c#gid=6

function building101Data_comprehensive() {

var form = document.forms.platformForm;

//BUILDING
if (form.contains(form.building_name)) {form.building_name.value = "Building 101";}
if (form.contains(form.weather_epw_location)) {form.weather_epw_location.value = "USA_PA_Philadelphia.Intl.AP.724080_TMY3";}
if (form.contains(form.activity_type)) {form.activity_type.value = "MediumOffice";}
if (form.contains(form.activity_type_specific)) {form.activity_type_specific.value = "Administrative/professional office";}
if (form.contains(form.year_completed)) {form.year_completed.value = "1911";}
if (form.contains(form.building_location_address)) {form.building_location_address.value = "4747 South Broad Street";}
if (form.contains(form.building_location_city)) {form.building_location_city.value = "Philadelphia";}
if (form.contains(form.building_location_state)) {form.building_location_state.value = "PA";}

//ARCHITECTURE
if (form.contains(form.gross_floor_area)) {form.gross_floor_area.value = 6967.72;} // attic & basement
if (form.contains(form.length)) {form.length.value = 90;} 
if (form.contains(form.width)) {form.width.value = 30;}
if (form.contains(form.building_height)) {form.building_height.value = 15.54;} //floor not attic
if (form.contains(form.building_orientation)) {form.building_orientation.value = 275;} //clockwise from North, degrees
if (form.contains(form.window_to_wall_ratio)) {form.window_to_wall_ratio.value = 15;}
if (form.contains(form.number_of_floors)) {form.number_of_floors.value = 4;} //conditioned space, includes basement
if (form.contains(form.perimeter)) {form.perimeter.value = 800;} 
if (form.contains(form.floor_to_floor_height)) {form.floor_to_floor_height.value = 3.66;} 


//ROOM
if (form.contains(form.exterior_shading_orientation)) {form.exterior_shading_orientation.value = "East";}
if (form.contains(form.room_width)) {form.room_width.value = 10;} //actual=71ft=21.6, reduced to half (10m) for daysim
if (form.contains(form.room_depth)) {form.room_depth.value = 9.37;}
if (form.contains(form.room_height)) {form.room_height.value = 4.04;}
if (form.contains(form.overhang_depth)) {form.overhang_depth.value = 0;}

//SHAPE
if (form.contains(form.footprint_shape)) {form.footprint_shape.value = "T";}
if (form.contains(form.end_1)) {form.end_1.value = 18.29;}
if (form.contains(form.end_2)) {form.end_2.value = 15.54;}
if (form.contains(form.offset)) {form.offset.value = 38.1;}

//MATERIALS
if (form.contains(form.exterior_wall_type)) {form.exterior_wall_type.value = "Concrete Mass";}
if (form.contains(form.wall_insulation_r_value)) {form.wall_insulation_r_value.value = "2";} //IP=4.6
if (form.contains(form.window_glass_type)) {form.window_glass_type.value = "Multi-layer Glass";} //Windows Double Glazed
if (form.contains(form.window_glass_coating)) {form.window_glass_coating.value = "clear";}
if (form.contains(form.roof_type)) {form.roof_type.value = "Metal";}

if (form.contains(form.roof_insulation_type)) {form.roof_insulation_type.value = "2";} //R=20
if (form.contains(form.roof_insulation_location)) {form.roof_insulation_location.value = "bottom";}

//PEOPLE
if (form.contains(form.people_density)) {form.people_density.value = 0.025;}
if (form.contains(form.number_of_occupants)) {form.number_of_occupants.value = 94;}
if (form.contains(form.number_of_employees_during_main_shift)) {form.number_of_employees_during_main_shift.value = 55;}

//LIGHTING
if (form.contains(form.illuminance)) {form.illuminance.value = 500;}
if (form.contains(form.lighting_power_density)) {form.lighting_power_density.value = 2;} //2W/sqft
if (form.contains(form.window_head_height)) {form.window_head_height.value = 3.12;} //10.25ft
if (form.contains(form.wall_thickness)) {form.wall_thickness.value = 1.5;}
if (form.contains(form.interior_shading_type)) {form.interior_shading_type.value = "Fabric_Shades";}
if (form.contains(form.floor_reflectance)) {form.floor_reflectance.value = 0.2;}
if (form.contains(form.ceiling_reflectance)) {form.ceiling_reflectance.value = 0.4;}
if (form.contains(form.wall_reflectance)) {form.wall_reflectance.value = 0.6;}

//MECHANICAL
if (form.contains(form.equipment_power_density)) {form.equipment_power_density.value = 15;} //1W/sqft
if (form.contains(form.ventilation_system)) {form.ventilation_system.value = "natural";}
if (form.contains(form.primary_hvac_type)) {form.primary_hvac_type.value = "SystemType5";}
if (form.contains(form.demand_control_ventilation)) {form.demand_control_ventilation.value = "no";}
if (form.contains(form.airside_economizer)) {form.airside_economizer.value = "no";}
if (form.contains(form.airside_energy_recovery)) {form.airside_energy_recovery.value = "no";}
}