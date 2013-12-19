#!/usr/bin/ruby -w
 
require 'openstudio'
require 'VirtualPULSEModel'
require 'AddIDFTables'

# save_dir is the directory where this Ruby script will put the ENERGYPLUS, osm, and idf folders in
# if this directory does not work on your machine, change it accordingly
save_dir = "/home/platform/openstudio/outputs"

#---------------------------------------- Read Input Parameters ----------------------------------------#
	
	# ------------ Command-line Parameters ------------- #
	in_username = ARGV.at(0).to_s
	in_email = ARGV.at(1).to_s
	in_building_name = ARGV.at(2).to_s
	in_city = ARGV.at(3).to_s
	tightness = ARGV.at(4).to_s
	
	in_primary_space_type = ARGV.at(5).to_s # no spaces
	in_secondary_space_type = ARGV.at(6).to_s # no spaces
	in_num_floors = ARGV.at(7).to_i
	in_total_floor_area = ARGV.at(8).to_f

	in_roof_material_name = ARGV.at(9).to_s
	in_wall_material_name = ARGV.at(10).to_s

	puts "%%%%%%%%%%%%%%%window ratio "
    puts in_wwr = ARGV.at(11).to_f
    puts "%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%"

   	puts in_geometry_type = ARGV.at(12).to_s


	size_hash = Hash.new
	size_hash['num_floors'] = ARGV.at(7).to_i
    puts "!!!! geometry type: #{in_geometry_type}!!!";

	case in_geometry_type
	when 'H'
		# for H shape sizes
		size_hash['length'] = ARGV.at(13).to_f
	    size_hash['left_width'] = ARGV.at(14).to_f
	    size_hash['center_width'] = ARGV.at(15).to_f
	    size_hash['right_width'] = ARGV.at(16).to_f
	    size_hash['left_end_length'] = ARGV.at(17).to_f
	    size_hash['right_end_length'] = ARGV.at(18).to_f
	  	
	  	size_hash['left_upper_end_offset'] = ARGV.at(19).to_f
	  	size_hash['right_upper_end_offset'] = ARGV.at(20).to_f
	when 'L'
		# for L shape sizes
		size_hash['length'] = ARGV.at(13).to_f
	    size_hash['width'] = ARGV.at(14).to_f
	    size_hash['end1'] = ARGV.at(15).to_f
	    size_hash['end2'] = ARGV.at(16).to_f
	when 'T'
		# for T shape sizes
		size_hash['length'] = ARGV.at(13).to_f
	    size_hash['width'] = ARGV.at(14).to_f
	    size_hash['end1'] = ARGV.at(15).to_f
	    size_hash['end2'] = ARGV.at(16).to_f
	    size_hash['offset'] = ARGV.at(17).to_f
	when 'U'
		# for U shape sizes
		size_hash['length'] = ARGV.at(13).to_f
	    size_hash['width1'] = ARGV.at(14).to_f
	    size_hash['width2'] = ARGV.at(15).to_f
	    size_hash['end1'] = ARGV.at(16).to_f
	    size_hash['end2'] = ARGV.at(17).to_f
	    size_hash['offset'] = ARGV.at(18).to_f
	when 'Pie'
		# for Pie/Circle/Ellipse shape sizes
		size_hash['radius_a'] = ARGV.at(13).to_f
	    size_hash['radius_b'] = ARGV.at(14).to_f
	    size_hash['num_points'] = ARGV.at(15).to_f
	    size_hash['degree'] = ARGV.at(16).to_f
	when 'Polygon'
		# for Polygon shape?? Get polygon points
		puts "Polygon shape has not been fully implemented yet. Simulation terminating ..."
		return false
	when 'Rectangle'
		# for Rectangle shape sizes
		size_hash['length'] = ARGV.at(13).to_f
	    size_hash['width'] = ARGV.at(14).to_f
	else
		puts "Geometry type not implemented yet. Simulation terminating ..."
		return false
	end

	in_principle_heating = 'NaturalGas' # no spaces

	submission_id = in_username  #1001
	location_weather_filename = in_city #"USA_IL_Chicago-OHare.Intl.AP.725300_TMY3"
#-------------------------------------------------------------------------------------------------------#
	# for geometry 
	num_floors = in_num_floors	# must be integer
	floor_to_floor_height = 3  	# must be 
	plenum_height = 1.0 		# 
	perimeter_zone_depth = 3.0 	# must be less than 

	# for windows
	wwr = in_wwr
	offset = 1
	application_type = 'Above Floor'
	
	# for HVAC
	fan_eff = 0.5
	boiler_eff = 0.66
	boiler_fuel_type = in_principle_heating
	coil_cool_rated_high_speed_COP = 3.5
	coil_cool_rated_low_speed_COP = 4.5
	economizer_type = 'No Economizer'
	economizer_dry_bulb_temp_limit = 30
	economizer_enthalpy_limit = 23

	# for thermostats
	heating_setpoint = 16
	cooling_setpoint = 24

	# for space type
	_NREL_reference_building_vintage = 'ASHRAE_90.1-2004'
	_Climate_zone = 'ClimateZone 1-8'
	_NREL_reference_building_primary_space_type = in_primary_space_type
	_NREL_reference_building_secondary_space_type = in_secondary_space_type

#---------------------------------------- Create Simulation Model and Run ----------------------------------#

	#create a new model
	model = VirtualPULSEModel.new

# ***************** redesign the parameter list *********************
	#add geometry (in this case a simple multi-story core/perimeter building)
	# two hashes in parameter list -- one for size data, another for building data
	model.add_geometry(size_hash,
						{"geo_type"=> in_geometry_type,
		            	"num_floors" => num_floors,
		            	"floor_to_floor_height" => floor_to_floor_height,
		            	"plenum_height" => plenum_height,
		            	"perimeter_zone_depth" => perimeter_zone_depth})

	puts "Geometry added. #{Time.now()}"

	#add windows at a given window-to-wall ratio
	model.add_windows({	"wwr" => wwr,
		          		"offset" => offset,
		          		"application_type" => application_type  #string
						})

	puts "Windows added. #{Time.now()}"

	#add HVAC - Packaged VAV w/ Reheat - DX Cooling, Hot Water heat and reheat
	model.add_hvac({"fan_eff" => fan_eff,
		      "boiler_eff" => boiler_eff,
		      "boiler_fuel_type" => boiler_fuel_type,		# string
		      "coil_cool_rated_high_speed_COP" => coil_cool_rated_high_speed_COP,
		      "coil_cool_rated_low_speed_COP" => coil_cool_rated_low_speed_COP,
		      "economizer_type" => economizer_type,			# string
		      "economizer_dry_bulb_temp_limit" => economizer_dry_bulb_temp_limit,
		      "economizer_enthalpy_limit" => economizer_enthalpy_limit})

	puts "HVAC added. #{Time.now()}"

	#add thermostats
	model.add_thermostats({		"heating_setpoint" => heating_setpoint,
		              			"cooling_setpoint" => cooling_setpoint})

	#assign constructions from a local library to the walls/windows/etc. in the model
	model.add_constructions({	"construction_library_path" => "#{Dir.pwd}/VirtualPULSE_default_constructions.osm",
								"degree_to_north" => 15.0})

	puts "Constructions added. #{Time.now()}"

	# no space between two words  e.g. SmallOffice WholeBuilding
	#add space type from a remote library (BCL) to the model
	model.add_space_type({	"NREL_reference_building_vintage" => _NREL_reference_building_vintage,
		            		"Climate_zone" => _Climate_zone,
		            		"NREL_reference_building_primary_space_type" => _NREL_reference_building_primary_space_type,
		            		"NREL_reference_building_secondary_space_type" => _NREL_reference_building_secondary_space_type})  

	puts "Space type added. #{Time.now()}"

	#add densities
	model.add_densities

	#upgrade windows
	#model.upgrade_windows

	#add design days to the model
	model.add_design_days({		"loc_filename" => location_weather_filename})

	puts "Designed days added. #{Time.now()}"

	#save the OpenStudio model (.osm)
	#model.save_openstudio_osm({	"osm_save_directory" => save_dir,
	#	                   		"osm_name" => "osm/Simulation_#{submission_id}.osm"})

####################################################################################################

	#puts"<br>######################### FitInfiltration Start ###################################### <br>" 
        model.save_openstudio_osm({     "osm_save_directory" => save_dir,
                                                "osm_name" => "osm/SavedModel_#{submission_id}.osm"})

        #Run CONTAM
        #Command Line Example: /FitInfiltration --l Leaky BBox-model-modified.osm -o output.osm
        airtightness = tightness
        run_infiltration = `FitInfiltration --level #{airtightness} -o #{save_dir}/osm/Simulation_#{submission_id}.osm #{save_dir}/osm/SavedModel_#{submission_id}.osm`

        #puts run_infiltration

        #puts"<br>######################### FitInfiltration End  ###################################### <br>"
#####################################################################################################

	# load a new model from the osm file	
	model1 = VirtualPULSEModel::load(OpenStudio::Path.new("#{save_dir}/osm/Simulation_#{submission_id}.osm")).get;

        #idf_save_directory = params["idf_save_directory"]
        #idf_name = params["idf_name"]

    	# make a forward translator and convert openstudio model to energyplus
    	forward_translator = OpenStudio::EnergyPlus::ForwardTranslator.new()
    	workspace = forward_translator.translateModel(model1)
    	idf_save_path = OpenStudio::Path.new("#{save_dir}/idf/Simulation_#{submission_id}.idf")
    	workspace.save(idf_save_path,true)

        # remove the temporary files
        `rm ./temporary*` 


=begin		                   
	# translate the OpenStudio model (.osm) to an EnergyPlus model (.idf)
	model1.translate_to_energyplus_and_save_idf({	"idf_save_directory" => save_dir,
		                                    		"idf_name" => "idf/Simulation_#{submission_id}.idf"})
=end


	#modify the idf file so that we can ask eplus to output monthly data, (Mujing Wang) 
	idf_file = File.new("#{save_dir}/idf/Simulation_#{submission_id}.idf", "a")
	add_monthly_electricity(idf_file)
	add_monthly_gasoline(idf_file)	
	add_GHG_emmission(idf_file) 
	add_energy_cost(idf_file)

	#natural gas or gasoline?		
	add_monthly_naturalgas(idf_file) 	# not displaying (maybe because the entries in this table are all zeros)

	idf_file.close

	# add zone component load summary
	add_load_summary_report("#{save_dir}/idf/Simulation_#{submission_id}.idf")

	# convert unit to inch-pound
	convert_unit_to_ip("#{save_dir}/idf/Simulation_#{submission_id}.idf")

	puts "IDF file modified.\n\nStart running EngergyPlus #{Time.now()}"
	
	#run the EnergyPlus model (.idf)
	VirtualPULSEModel::run_energyplus_simulation({	"idf_directory" => save_dir,
		                                      		"idf_name" => "idf/Simulation_#{submission_id}.idf",
													"epw_filename" => location_weather_filename,
													"seq_num" => "#{submission_id}"})

puts "EnergyPlus finished. #{Time.now()}"

# save infiltration output message 
`echo "#{run_infiltration}" > #{save_dir}/ENERGYPLUS/idf/Simulation_#{submission_id}.idf/EnergyPlusPreProcess/EnergyPlus-0/infiltration_msg.txt`



# ----------------------------------------------------------------------------------------------------------- #
# Testing the output files
=begin
puts "<br>############################################################################################<br>"
puts "<h1>Model: Simulation_#{submission_id}</h1><br>"
puts "Time: #{Time.now()}<br>"
puts "Osm file: <a href='http://128.118.67.241/openstudio/outputs/osm/Simulation_#{submission_id}.osm' target='_blank'>Simulation_#{submission_id}.osm</a><br>"
puts "Idf file: <a href='http://128.118.67.241/openstudio/outputs/idf/Simulation_#{submission_id}.idf' target='_blank'>Simulation_#{submission_id}.idf</a><br>"
puts "Html file: <a href='http://128.118.67.241/openstudio/outputs/ENERGYPLUS/idf/Simulation_#{submission_id}.idf/EnergyPlusPreProcess/EnergyPlus-0/eplustbl.htm' target='_blank'>Simulation_#{submission_id}.html</a><br>"
puts "Infiltration message file: <a href='http://128.118.67.241/openstudio/outputs/ENERGYPLUS/idf/Simulation_#{submission_id}.idf/EnergyPlusPreProcess/EnergyPlus-0/infiltration_msg.txt' target='_blank'>infiltration_message.txt</a><br>"
=end









