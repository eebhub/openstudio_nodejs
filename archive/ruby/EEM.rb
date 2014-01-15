# /usr/bin/ruby1.8 -w

# EEM1:  update lighting_power_density
# EEM2:  update window_conductivity
# EEM3:  update economizer_control_type



require 'openstudio'
require 'VirtualPULSEModel'


#---------------------------------------- Run Simulation In Eplus8.0----------------------------------#

def run_simulation(params)

	# input
	save_dir = params["save_dir"]
	idf_name = params["idf_name"]
	location_weather_filename = params["location_weather_filename"]
	submission_id = 0;

	#create a new model
	model = VirtualPULSEModel.new

	#run the EnergyPlus model (.idf)
	VirtualPULSEModel::run_energyplus_simulation({	"idf_directory" => save_dir,
			                                  		"idf_name" => "idf/#{idf_name}.idf",
													"epw_filename" => location_weather_filename,
													"seq_num" => "#{submission_id}"})
	puts "EnergyPlus finished. #{Time.now()}"
end


# ---------------------------------- Update EEM 1 ---------------------------------------------------- #


# this funciton updates the following information from idf file 
#   the lighting density, the start month and day and the end month and day
def update_lighting(params)

    # input params
	idf_path = params['idf_path']
    idf_file = params['idf_file']
    light_density = params["light_density"]
    start_month = params["start_month"]
    start_day = params["start_day"]
    end_month = params["end_month"]
    end_day = params["end_day"]

    # indicator that shows the current line is in the lights field
    # 0 if the current line is in the lights field, otherwise, 1 
    in_light_field = 0

  
    # file pointer that opens idf file for modification
    f1 = File.open("#{idf_path}/#{idf_file}", 'r+')

    # file pointer that writes a copy of EEM 1
    f2 = File.new("#{idf_path}/EEM1_#{idf_file}", "w+" )
    
    # go through each line of f1 to modify lighting_power_density, the start/end of both month and day
    while line = f1.gets 

        # print out each line
        #puts "File 1: #{line}"
  

        ##############################################################
        # task1: update the lighting power density                   #
        ##############################################################

        # jump to the head of lights field in idf
        if(line =~ /^Lights,$/)
            f2.write(line)
            in_lights_field = 1

        # modify the lighting power density here
        elsif (in_lights_field == 1 && line =~ /^.*Watts per.*Floor Area.*$/) 
            f2.write(line.sub(/[0-9]+\.*[0-9]*/, light_density.to_s))

        # the end of lights field	
	elsif (in_lights_field == 1 && line =~ /.*;.*$/) 
            f2.write(line)
            in_lights_field = 0;


	    ####################################################################
        # task 2: update the start/end of month and day                    #
        ####################################################################
        
        # update the start month 
        elsif (line =~ /Begin Month$/)
            f2.write(line.sub( /.*/ , "  #{start_month},\t\t\t\t\t!- Begin Month"))	

        # update the start day of month
        elsif (line =~ /Begin Day of Month$/)
            f2.write(line.sub( /.*/ , "  #{start_day},\t\t\t\t\t!- Begin Day of Month"))

        # update the end month
        elsif (line =~ /End Month$/)
            f2.write(line.sub( /.*/, "   #{end_month},\t\t\t\t\t!- End Month"))

        # update the end day of month
        elsif (line =~ /End Day of Month$/)
            f2.write(line.sub(/.*/, "   #{end_day},\t\t\t\t\t!- End Day of Month"))
 
        else 
            f2.write(line)
        end

    end

    f1.close
    f2.close
end

# ---------------------------------------- Update EEM 2 --------------------------------------------- #
# this funciton updates the following information from idf file 
#   the window conductivity, the start month and day and the end month and day
def update_window_conductivity(params)

    # input params
	idf_path = params['idf_path']
    idf_file = params['idf_file']
    window_conductivity = params["window_conductivity"]
    start_month = params["start_month"]
    start_day = params["start_day"]
    end_month = params["end_month"]
    end_day = params["end_day"]

    # indicator that shows the current line is in the window_material field
    # 0 if the current line is in the window field, otherwise, 1 
    in_window_field = 0

  
    # file pointer that opens idf file for modification
    f1 = File.open("#{idf_path}/#{idf_file}", 'r+')

    # file pointer that writes a copy of EEM 2
    f2 = File.new("#{idf_path}/EEM2_#{idf_file}", "w+" )
    
    # go through each line of f1 to modify window conductivity, the start/end of both month and day
    while line = f1.gets 

        # print out each line
        #puts "File 1: #{line}"
  

        ##############################################################
        # task1: update the window conductivity                      #
        ##############################################################

        # jump to the head of window field in idf
        if(line =~ /^WindowMaterial:Glazing,$/)
            f2.write(line)
            in_window_field = 1

        # modify the window conductivity here
        elsif (in_window_field == 1 && line =~ /^.*Conductivity.*$/) 
			f2.write(line.sub(/[0-9]\.*[0-9]+/, window_conductivity.to_s))

        # the end of window field	
	    elsif (in_window_field == 1 && line =~ /.*;.*$/) 
            f2.write(line)
            in_window_field = 0;


	    ####################################################################
        # task 2: update the start/end of month and day                    #
        ####################################################################
        
        # update the start month 
        elsif (line =~ /Begin Month$/)
            f2.write(line.sub( /.*/ , "  #{start_month},\t\t\t\t\t!- Begin Month"))	

        # update the start day of month
        elsif (line =~ /Begin Day of Month$/)
            f2.write(line.sub( /.*/ , "  #{start_day},\t\t\t\t\t!- Begin Day of Month"))

        # update the end month
        elsif (line =~ /End Month$/)
            f2.write(line.sub( /.*/, "   #{end_month},\t\t\t\t\t!- End Month"))

        # update the end day of month
        elsif (line =~ /End Day of Month$/)
            f2.write(line.sub(/.*/, "   #{end_day},\t\t\t\t\t!- End Day of Month"))
 
        else 
            f2.write(line)
        end

    end

    f1.close
    f2.close
end


# ------------------------------------- Update EEM 3 --------------------------------------------- #
# this funciton updates the following information from idf file 
#   the economizer control type, the start month and day and the end month and day
def update_economizer_control_type(params)

    # input params
	idf_path = params['idf_path']
    idf_file = params['idf_file']
    control_type = params["control_type"]
    start_month = params["start_month"]
    start_day = params["start_day"]
    end_month = params["end_month"]
    end_day = params["end_day"]

    # file pointer that opens idf file for modification
    f1 = File.open("#{idf_path}/#{idf_file}", 'r+')

    # file pointer that writes a copy of EEM 3
    f2 = File.new("#{idf_path}/EEM3_#{idf_file}", "w+" )
    
    # go through each line of f1 to modify Economizer Control Type, the start/end of both month and day
    while line = f1.gets 

        # print out each line
        #puts "File 1: #{line}"
  

        ##############################################################
        # task1: update the Economizer Control Type                  #
        ##############################################################


        # modify the Economizer Control Type here
        if (line =~ /^.*Economizer Control Type.*$/) 
			#puts line
			f2.write(line.sub(/[A-Z][a-z]*[A-Z]*[a-z]*/, control_type.to_s))


	    ####################################################################
        # task 2: update the start/end of month and day                    #
        ####################################################################
        
        # update the start month 
        elsif (line =~ /Begin Month$/)
            f2.write(line.sub( /.*/ , "  #{start_month},\t\t\t\t\t!- Begin Month"))	

        # update the start day of month
        elsif (line =~ /Begin Day of Month$/)
            f2.write(line.sub( /.*/ , "  #{start_day},\t\t\t\t\t!- Begin Day of Month"))

        # update the end month
        elsif (line =~ /End Month$/)
            f2.write(line.sub( /.*/, "   #{end_month},\t\t\t\t\t!- End Month"))

        # update the end day of month
        elsif (line =~ /End Day of Month$/)
            f2.write(line.sub(/.*/, "   #{end_day},\t\t\t\t\t!- End Day of Month"))
 
        else 
            f2.write(line)
        end

    end

    f1.close
    f2.close
end


##########################################################################################################
#   Sample Run                                                                                           #
##########################################################################################################
=begin
# test method
idf_path = "/home/platform/openstudio/outputs/idf" 			# input file path
idf_file = "in.idf"                                         # input file name


light_density = 9											# EEM 1 new lighting power density
window_conductivity = 0.7									# EEM 2 window conductivity
control_type = "FixedDryBulb"								# EEM 3	economizer control type

start_month = 1
start_day = 1
end_month = 9
end_day = 30


update_lighting( "idf_path" => idf_path,
				 "idf_file" => idf_file,
                 "light_density" => light_density,
                 "start_month" => start_month,
                 "start_day" => start_day,
                 "end_month" => end_month,
                 "end_day" => end_day)


update_window_conductivity( "idf_path" => idf_path,
							"idf_file" => idf_file,
							"window_conductivity" => window_conductivity,
							"start_month" => start_month,
							"start_day" => start_day,
							"end_month" => end_month,
							"end_day" => end_day)


update_economizer_control_type( "idf_path" => idf_path,
								"idf_file" => idf_file,
								"control_type" => control_type,
								"start_month" => start_month,
								"start_day" => start_day,
								"end_month" => end_month,
								"end_day" => end_day)
=end
