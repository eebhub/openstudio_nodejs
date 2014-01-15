# modified for accepting parameters
# for E+ 8.0
# Mujing Wang
# April 2013
# updated on August 6 2013

class VirtualPULSEModel < OpenStudio::Model::Model

  def add_geometry(size_params, building_params)
#*****************
    geo_type = building_params['geo_type']
    merged_params = Hash.new
    merged_params = size_params.merge(merged_params)
    merged_params = building_params.merge(merged_params)

    case geo_type
    when 'H'
      require 'Set_H-Shape_Floor_Plan'
      shape_plan = SetHShapeFloorPlan.new
      shape_plan.add_geometry_H(self, merged_params)
    when 'L'
      require 'Set_L-Shape_Floor_Plan'
      shape_plan = SetLShapeFloorPlan.new
      shape_plan.add_geometry_L(self, merged_params)
    when 'T'
      require 'Set_T-Shape_Floor_Plan'
      shape_plan = SetTShapeFloorPlan.new
      shape_plan.add_geometry_T(self, merged_params)
    when 'U'
      require 'Set_U-Shape_Floor_Plan'
      shape_plan = SetUShapeFloorPlan.new
      shape_plan.add_geometry_U(self, merged_params)
    when 'Pie'
      require 'Set_Pie-Shape_Floor_Plan'
      shape_plan = SetPieShapeFloorPlan.new
      shape_plan.add_geometry_pie(self, merged_params)
    when 'Polygon'
      # Not fully implemented yet!
      require 'Set_Polygon-Shape_Floor_Plan'
      shape_plan = SetPolygonShapeFloorPlan.new
      shape_plan.add_geometry_generic(self, merged_params)
    when 'Rectangle'
      require 'Set_Rectangle-Shape_Floor_Plan'
      shape_plan = SetRectangleShapeFloorPlan.new
      shape_plan.add_geometry_rectangle(self, merged_params)
    else
      puts "Geometry type not implemented yet."
      return false
    end

#******   
    #Put all of the spaces in the model into a vector
    spaces = OpenStudio::Model::SpaceVector.new
    self.getSpaces.each { |space| spaces << space }

    #Match surfaces for each space in the vector
    OpenStudio::Model.matchSurfaces(spaces) # Match surfaces and sub-surfaces within spaces
    
    #Apply a thermal zone to each space in the model if that space has no thermal zone already
    self.getSpaces.each do |space|
      if space.thermalZone.empty?
        new_thermal_zone = OpenStudio::Model::ThermalZone.new(self)
        space.setThermalZone(new_thermal_zone)
      end
    end # end space loop
#******
    
  end # end add_geometry method
  
  def add_windows(params)
    puts wwr = params["wwr"]
    offset = params["offset"]
    application_type = params["application_type"]
    
    #input checking
    if not wwr or not offset or not application_type
      return false
    end

    if wwr <= 0 or wwr >= 1
      return false
    end

    if offset <= 0
      return false
    end

    heightOffsetFromFloor = nil
    if application_type == "Above Floor"
      heightOffsetFromFloor = true
    else
      heightOffsetFromFloor = false
    end
    
    self.getSurfaces.each do |s|
      next if not s.outsideBoundaryCondition == "Outdoors"
      new_window = s.setWindowToWallRatio(wwr, offset, heightOffsetFromFloor)
    end
  
  end

  def add_hvac(params)
    fan_eff = params["fan_eff"]
    boiler_eff = params["boiler_eff"]
    boiler_fuel_type = params["boiler_fuel_type"]
    coil_cool_rated_high_speed_COP = params["coil_cool_rated_high_speed_COP"]
    coil_cool_rated_low_speed_COP = params["coil_cool_rated_low_speed_COP"]
    economizer_type = params["economizer_type"]
    economizer_dry_bulb_temp_limit = params["economizer_dry_bulb_temp_limit"]
    economizer_enthalpy_limit = params["economizer_enthalpy_limit"]
    
    if fan_eff < 0 or fan_eff > 1
      return false
    end

    if boiler_eff < 0 or boiler_eff > 1
      return false
    end

    if coil_cool_rated_high_speed_COP < 0
      return false
    end
    
    if coil_cool_rated_low_speed_COP < 0
      return false
    end

    #get the thermal zones in the self
    zones = self.getThermalZones
    
    #add a system type 5 - Packaged VAV with Reheat to the self and hook up
    #each zone to this system
    hvac = OpenStudio::Model::addSystemType5(self)
    hvac = hvac.to_AirLoopHVAC.get      
    zones.each do|zone|
      hvac.addBranchForZone(zone)      
    end
    #set the fan efficiency
    supply_fan = hvac.supplyComponents("OS:Fan:VariableVolume".to_IddObjectType)[0]
    supply_fan = supply_fan.to_FanVariableVolume.get
    supply_fan.setFanEfficiency(fan_eff)
    #set the boiler efficiency and fuel type
    heating_coil = hvac.supplyComponents("OS:Coil:Heating:Water".to_IddObjectType)[0]
    plant_loop = heating_coil.to_CoilHeatingWater.get.plantLoop.get
    boiler = plant_loop.supplyComponents("OS:Boiler:HotWater".to_IddObjectType)[0]
    boiler = boiler.to_BoilerHotWater.get
    boiler.setFuelType(boiler_fuel_type)
    boiler.setNominalThermalEfficiency(boiler_eff)
    #set the cooling coil low speed and high speed COP 
    cooling_coil = hvac.supplyComponents("OS:Coil:Cooling:DX:TwoSpeed".to_IddObjectType)[0]
    cooling_coil = cooling_coil.to_CoilCoolingDXTwoSpeed.get
    cooling_coil.setRatedHighSpeedCOP(coil_cool_rated_high_speed_COP)
    cooling_coil.setRatedLowSpeedCOP(coil_cool_rated_low_speed_COP)
    #set the economizer up
    case economizer_type
      when "No Economizer" :
        #do nothing
      when "Fixed Dry Bulb Temperature Limit" :
        outdoor_air_system = hvac.supplyComponents(OpenStudio::Model::AirLoopHVACOutdoorAirSystem::iddObjectType())[0]
        outdoor_air_system = outdoor_air_system.to_AirLoopHVACOutdoorAirSystem.get
        outdoor_air_controller = outdoor_air_system.getControllerOutdoorAir 
        outdoor_air_controller = outdoor_air_controller.to_ControllerOutdoorAir.get
        outdoor_air_controller.setEconomizerControlType("FixedDryBulb")
        outdoor_air_controller.setEconomizerMaximumLimitDryBulbTemperature(economizer_dry_bulb_temp_limit)
      when "Fixed Enthalpy Limit" :
        outdoor_air_system = hvac.supplyComponents(OpenStudio::Model::AirLoopHVACOutdoorAirSystem::iddObjectType())[0]
        outdoor_air_system = outdoor_air_system.to_AirLoopHVACOutdoorAirSystem.get
        outdoor_air_controller = outdoor_air_system.getControllerOutdoorAir 
        outdoor_air_controller = outdoor_air_controller.to_ControllerOutdoorAir.get
        outdoor_air_controller.setEconomizerControlType("FixedEnthalpy")
        outdoor_air_controller.setEconomizerMaximumLimitEnthalpy(economizer_enthalpy_limit)          
      else
        puts "#{economizer_type} is not a valid selection for economizer type."
    end
      
  end

  def add_constructions(params)
    construction_library_path = params["construction_library_path"]
	  degree_to_north = params["degree_to_north"]
  
    #input error checking
    if not construction_library_path
      return false
    end

    #make sure the file exists on the filesystem; if it does, open it
    construction_library_path = OpenStudio::Path.new(construction_library_path)
    if OpenStudio::exists(construction_library_path)
      construction_library = OpenStudio::IdfFile::load(construction_library_path, "OpenStudio".to_IddFileType).get
    else
      puts "#{construction_library_path} couldn't be found"
    end

    #add the objects in the construction library to the model
    self.addObjects(construction_library.objects)
    
    #apply the newly-added construction set to the model
    building = self.getBuilding
    default_construction_set = OpenStudio::Model::getDefaultConstructionSets(self)[0]
    building.setDefaultConstructionSet(default_construction_set)
	building.setNorthAxis(degree_to_north)
  
  end
  
  def add_space_type(params)
  	OpenStudio::Application::instance()
  	
	  localblc = OpenStudio::LocalBCL::instance(OpenStudio::Path.new("/home/virtualpulse"))

	bcl = OpenStudio::RemoteBCL.new
  	#puts bcl.setProdAuthKey("xsxYuim9hvuuGdVFvhM5GBxNLPnDmNgE")  

    nrel_reference_building_vintage = params["NREL_reference_building_vintage"]
    climate_zone = params["Climate_zone"]
    nrel_reference_building_primary_space_type = params["NREL_reference_building_primary_space_type"]
    nrel_reference_building_secondary_space_type = params["NREL_reference_building_secondary_space_type"]
  
    remoteBCL = OpenStudio::RemoteBCL.new
    
    remoteBCL.downloadOnDemandGenerator("bb8aa6a0-6a25-012f-9521-00ff10704b07");
    generator = remoteBCL.waitForOnDemandGenerator();
    if generator.empty?
		puts "generator empty"
      return false    
    end
    generator = generator.get

    generator.setArgumentValue("NREL_reference_building_vintage", nrel_reference_building_vintage)
    generator.setArgumentValue("Climate_zone", climate_zone)
    generator.setArgumentValue("NREL_reference_building_primary_space_type", nrel_reference_building_primary_space_type)
    generator.setArgumentValue("NREL_reference_building_secondary_space_type", nrel_reference_building_secondary_space_type)
    
    puts "#{nrel_reference_building_vintage}"
    puts "#{climate_zone}"
    puts "#{nrel_reference_building_primary_space_type}"
    puts "#{nrel_reference_building_secondary_space_type}"

    versionTranslator = OpenStudio::OSVersion::VersionTranslator.new
    
    #first check local library for the component
    component = OpenStudio::LocalBCL::instance().getOnDemandComponent(generator)

    # not in local library, download it
    if component.empty?
      puts "Space type not found locally, downloading"
      component = remoteBCL.getOnDemandComponent(generator)
    end

    if component.empty?
      puts "component not downloaded"
      return false
    end
    component = component.get

    oscFiles = component.files("osc")
    if oscFiles.empty?
      puts "No osc files found"
    end

    oscPath = OpenStudio::Path.new(oscFiles[0])
    modelComponent = versionTranslator.loadComponent(oscPath)

    if modelComponent.empty?
      puts "Could not load component"
    end
    modelComponent = modelComponent.get

    self.insertComponent(modelComponent)

    space_type = self.getObjectsByType("OS_SpaceType".to_IddObjectType)[0].to_SpaceType.get
    #set the space type of all spaces by setting it at the building level
    self.getBuilding.setSpaceType(space_type)

  end

  def add_thermostats(params)
    
    heating_setpoint = params["heating_setpoint"]
    cooling_setpoint = params["cooling_setpoint"]
    
    time_24hrs = OpenStudio::Time.new(0,24,0,0)

    cooling_sch = OpenStudio::Model::ScheduleRuleset.new(self)
    cooling_sch.setName("Cooling Sch")
    cooling_sch.defaultDaySchedule.setName("Cooling Sch Default")
    cooling_sch.defaultDaySchedule.addValue(time_24hrs,cooling_setpoint)

    heating_sch = OpenStudio::Model::ScheduleRuleset.new(self)
    heating_sch.setName("Heating Sch")
    heating_sch.defaultDaySchedule.setName("Heating Sch Default")
    heating_sch.defaultDaySchedule.addValue(time_24hrs,heating_setpoint)      

    new_thermostat = OpenStudio::Model::ThermostatSetpointDualSetpoint.new(self)
    
    new_thermostat.setHeatingSchedule(heating_sch)
    new_thermostat.setCoolingSchedule(cooling_sch)
    
    self.getThermalZones.each do |zone|
      zone.setThermostatSetpointDualSetpoint(new_thermostat)
    end

  end  
  
  def add_densities
    # add the outdoor air fraction
    # get the DesignSpecificationOutdoorAirs for the entire building; the spaceType command does not work
    ventilation=self.getDesignSpecificationOutdoorAirs 
        
    # change the outdoor air from per person 20 ft^3/min-person (BCL default value) to ACH 
    ventilation.first.setOutdoorAirFlowAirChangesperHour(1.0)      
    # override the per person 20 ft^3/min-person (BCL default value) to reduced number 7 ft^3/min-person 
    #ventilation.first.setOutdoorAirFlowperPerson(0.0025)     

  end

=begin
  def upgrade_windows
	low_e_glass = OpenStudio::Model::SimpleGlazing.new(self)
	low_e_glass.setName("Low-E Glass")
	low_e_glass.setUFactor(0.1524) #meters
	low_e_glass.setSolarHeatGainCoefficient(1.95) #W/m*K
	low_e_glass.setVisibleTransmittance(2242.6) #kg/m^3
  end
=end

  def save_openstudio_osm(params)
  
    osm_save_directory = params["osm_save_directory"]
    osm_name = params["osm_name"]
  
    save_path = OpenStudio::Path.new("#{osm_save_directory}/#{osm_name}")
    self.save(save_path,true)
    
  end
  
  def translate_to_energyplus_and_save_idf(params)
  
    idf_save_directory = params["idf_save_directory"]
    idf_name = params["idf_name"]
  
    #make a forward translator and convert openstudio model to energyplus
    forward_translator = OpenStudio::EnergyPlus::ForwardTranslator.new()
    workspace = forward_translator.translateModel(self)
    idf_save_path = OpenStudio::Path.new("#{idf_save_directory}/#{idf_name}")
    workspace.save(idf_save_path,true)
  
  end

  def add_design_days(params)
      
    require 'openstudio/energyplus/find_energyplus'

	loc_filename = params["loc_filename"]
     
    # find energyplus
    ep_hash = OpenStudio::EnergyPlus::find_energyplus(8,0) 	
    weather_path = OpenStudio::Path.new(ep_hash[:energyplus_weatherdata].to_s)
      
    #load the design days for Chicago
    ddy_path = OpenStudio::Path.new("#{weather_path}/#{loc_filename}.idf") 
    
	#make sure the file exists on the filesystem; if it does, open it
    if OpenStudio::exists(ddy_path)
      ddy_idf = OpenStudio::IdfFile::load(ddy_path, "EnergyPlus".to_IddFileType).get
      ddy_workspace = OpenStudio::Workspace.new(ddy_idf)
      reverse_translator = OpenStudio::EnergyPlus::ReverseTranslator.new()
      ddy_model = reverse_translator.translateWorkspace(ddy_workspace)
      #add the objects in the ddy file to the model
      self.addObjects(ddy_model.objects)
    else
      puts "#{ddy_path} couldn't be found"
    end  

  end

  def self.run_energyplus_simulation(params)
    
    require 'openstudio/energyplus/find_energyplus'

    idf_directory = params["idf_directory"]
	epw_filename = params['epw_filename']
    idf_name = params["idf_name"]
     
    # find energyplus
    ep_hash = OpenStudio::EnergyPlus::find_energyplus(8,0) 
    weather_path = OpenStudio::Path.new(ep_hash[:energyplus_weatherdata].to_s)

    # just run in Chicago for now
    epw_path = OpenStudio::Path.new("#{weather_path.to_s}/#{epw_filename}.epw")
        
    # make a run manager
    run_manager_db_path = OpenStudio::Path.new("#{idf_directory}/VirtualPULSE.db")
    run_manager = OpenStudio::Runmanager::RunManager.new(run_manager_db_path, true)

    #get the run manager configuration options
    config_options = run_manager.getConfigOptions()
    config_options.fastFindEnergyPlus()
    tools = config_options.getTools()
    
    # assert !tools.getAllByName("energyplus").tools().empty()

    sys_num_array = Array.new

    idf_path = OpenStudio::Path.new("#{idf_directory}/#{idf_name}")
    
    output_path = OpenStudio::Path.new("#{idf_directory}/ENERGYPLUS/#{idf_name}")


    #make the ENERGYPLUS directory to store the results
    output_path_string = File.dirname(output_path.to_s)
      
    # make a job for the file we want to run
    workflow = OpenStudio::Runmanager::Workflow.new("EnergyPlusPreProcess->EnergyPlus")
    workflow.add(tools)
    job = workflow.create(output_path, idf_path, epw_path)
    
    
    #put the job in the run queue
    run_manager.enqueue(job, true)

    # wait for jobs to complete
    while run_manager.workPending()
      sleep 1
      OpenStudio::Application::instance().processEvents()
    end

  end  
  
end #end clas