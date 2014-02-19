#-RETROFIT-MANAGER-2014-------------------------------------------------------------------------#
require 'openstudio'
require 'OpenstudioModel'
require 'fileutils' #needed for run_eebhub.rb & autosize.rb
require 'tempfile' #needed for autosize.rb
#-run_eebhub.rb---------------------------------------------------------------------------------#
save_dir = "/home/jason/outputs"
FileUtils.mkdir_p save_dir
#-INPUT VARIABLES-------##-Need to stay to understand what the variables are > make flexible order in JavaScript
	in_username = ARGV.at(0).to_s
	in_email = ARGV.at(1).to_s
	in_building_name = ARGV.at(2).to_s
	in_city = ARGV.at(3).to_s
	in_primary_space_type = ARGV.at(4).to_s # no spaces
	in_secondary_space_type = ARGV.at(5).to_s # no spaces
	in_num_floors = ARGV.at(6).to_i
	in_total_floor_area = ARGV.at(7).to_f #not being used currently.  only passing for future use.
	in_roof_material_name = ARGV.at(8).to_s
	in_wall_material_name = ARGV.at(9).to_s
    puts in_wwr = ARGV.at(10).to_f
   	puts in_geometry_type = ARGV.at(11).to_s
	size_hash = Hash.new
	size_hash['num_floors'] = ARGV.at(6).to_i
	case in_geometry_type
	when 'Rectangle'
		size_hash['length'] = ARGV.at(12).to_f
	    size_hash['width'] = ARGV.at(13).to_f
	else
		puts "Geometry type not implemented yet. Simulation terminating ..."
		return false
	end
	submission_id = in_username  #1001
	location_weather_filename = in_city #"USA_IL_Chicago-OHare.Intl.AP.725300_TMY3"
	num_floors = in_num_floors	# must be integer
#-INPUT CONSTANTS----------#
	in_principle_heating = 'NaturalGas' # no spaces
	boiler_fuel_type = in_principle_heating
	plenum_height = 1.0 		# 
	perimeter_zone_depth = 3.0 	# must be less than 
	wwr = in_wwr
	offset = 1
	application_type = 'Above Floor'
	boiler_eff = 0.66
	economizer_type = 'No Economizer'
	_NREL_reference_building_vintage = 'ASHRAE_90.1-2004'
	_Climate_zone = 'ClimateZone 1-8'
	_NREL_reference_building_primary_space_type = in_primary_space_type
	_NREL_reference_building_secondary_space_type = in_secondary_space_type
    stringent_sizing_criteria="yes"
    
#-OPENSTUDIO MODEL-----------------------------------------------#
	model = OpenstudioModel.new
#-OPENSTUDIO - Pass inputs
	model.add_geometry(size_hash,
						{"geo_type"=> in_geometry_type,
		            	"num_floors" => num_floors,
		            	"floor_to_floor_height" => 3,
		            	"plenum_height" => plenum_height,
		            	"perimeter_zone_depth" => perimeter_zone_depth})
	model.add_windows({	"wwr" => wwr,
		          		"offset" => offset,
		          		"application_type" => application_type})
	model.add_hvac({"fan_eff" => 0.5,
		      "boiler_eff" => boiler_eff,
		      "boiler_fuel_type" => boiler_fuel_type,		# string
		      "coil_cool_rated_high_speed_COP" => 3.5,
		      "coil_cool_rated_low_speed_COP" => 4.5,
		      "economizer_type" => economizer_type,			# string
		      "economizer_dry_bulb_temp_limit" => 30})
	model.add_thermostats({		"heating_setpoint" => 20,
		              			"cooling_setpoint" => 24})
	model.add_constructions({	"construction_library_path" => "#{Dir.pwd}/VirtualPULSE_default_constructions.osm",
								"degree_to_north" => 15.0})
	model.add_space_type({	"NREL_reference_building_vintage" => _NREL_reference_building_vintage,
		            		"Climate_zone" => _Climate_zone,
		            		"NREL_reference_building_primary_space_type" => _NREL_reference_building_primary_space_type,
		            		"NREL_reference_building_secondary_space_type" => _NREL_reference_building_secondary_space_type})  
	model.add_densities()
	model.add_design_days({	"loc_filename" => location_weather_filename,
							"stringent_sizing_criteria" => stringent_sizing_criteria})

	model.save_openstudio_osm({"osm_save_directory" => "#{save_dir}",
      							"osm_name" => "Simulation_#{submission_id}.osm"})
	model.translate_to_energyplus_and_save_idf({"idf_save_directory" => "#{save_dir}",
  												"idf_name" => "Simulation_#{submission_id}.idf"})

	idf_file = File.new("#{save_dir}/Simulation_#{submission_id}.idf", "a")
	model.add_load_summary_report("#{save_dir}/Simulation_#{submission_id}.idf")
	model.convert_unit_to_ip("#{save_dir}/Simulation_#{submission_id}.idf")
	idf_file.close
	puts "IDF file modified.\n\nStart running EngergyPlus #{Time.now()}"
	OpenstudioModel::run_energyplus_simulation({	"idf_directory" => "#{save_dir}",
		                                      		"idf_name" => "Simulation_#{submission_id}.idf",
													"epw_filename" => location_weather_filename,
													"seq_num" => "#{submission_id}"})
