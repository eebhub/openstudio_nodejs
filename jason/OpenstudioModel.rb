#-VirtualPULSEModel.rb--------------------------------------------------------------------------#
class OpenstudioModel < OpenStudio::Model::Model
  def add_geometry(size_params, building_params)
    geo_type = building_params['geo_type']
    merged_params = Hash.new
    merged_params = size_params.merge(merged_params)
    merged_params = building_params.merge(merged_params)
    case geo_type
    when 'Rectangle'
      require 'Set_Rectangle-Shape_Floor_Plan'
      shape_plan = SetRectangleShapeFloorPlan.new
      shape_plan.add_geometry_rectangle(self, merged_params)
    else
      puts "Geometry type not implemented yet."
      return false
    end
    spaces = OpenStudio::Model::SpaceVector.new
    self.getSpaces.each { |space| spaces << space }
    OpenStudio::Model.matchSurfaces(spaces) # Match surfaces and sub-surfaces within spaces
    self.getSpaces.each do |space|
      if space.thermalZone.empty?
        new_thermal_zone = OpenStudio::Model::ThermalZone.new(self)
        space.setThermalZone(new_thermal_zone)
      end
    end # end space loop
  end # end add_geometry method
  def add_windows(params)
    puts wwr = params["wwr"]
    offset = params["offset"]
    application_type = params["application_type"]
    heightOffsetFromFloor = nil
    if application_type == "Above Floor"
      heightOffsetFromFloor = true
      heightOffsetFromFloor = false
    end
    self.getSurfaces.each do |s|
      next if not s.outsideBoundaryCondition == "Outdoors"
      new_window = s.setWindowToWallRatio(wwr, offset, heightOffsetFromFloor)
    end
  end
    #-AddIDFTables.rb-------------------------------------------------------------------------------#
    # add ZoneComponentLoadSummary to summaryreport in idf_file
    def add_load_summary_report(idf_file)
      puts `sed  's/\\(Output:Table:SummaryReports,\\)/\\1\\n  ZoneComponentLoadSummary,/g'  #{idf_file} > #{idf_file}_new`
      `mv #{idf_file}_new #{idf_file}`
    end
    # modify the default unit system to I-P unit in idf_file  
    def convert_unit_to_ip(idf_file)
      puts `sed  's/\\(HTML;.*\\)/HTML,\\n  InchPound;/g'  #{idf_file} > #{idf_file}_new`
      `mv #{idf_file}_new #{idf_file}`
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
    zones = self.getThermalZones
    hvac = OpenStudio::Model::addSystemType5(self)
    hvac = hvac.to_AirLoopHVAC.get      
    zones.each do|zone|
      hvac.addBranchForZone(zone)      
    end
    supply_fan = hvac.supplyComponents("OS:Fan:VariableVolume".to_IddObjectType)[0]
    supply_fan = supply_fan.to_FanVariableVolume.get
    supply_fan.setFanEfficiency(fan_eff)
    heating_coil = hvac.supplyComponents("OS:Coil:Heating:Water".to_IddObjectType)[0]
    plant_loop = heating_coil.to_CoilHeatingWater.get.plantLoop.get
    boiler = plant_loop.supplyComponents("OS:Boiler:HotWater".to_IddObjectType)[0]
    boiler = boiler.to_BoilerHotWater.get
    boiler.setFuelType(boiler_fuel_type)
    boiler.setNominalThermalEfficiency(boiler_eff)
    cooling_coil = hvac.supplyComponents("OS:Coil:Cooling:DX:TwoSpeed".to_IddObjectType)[0]
    cooling_coil = cooling_coil.to_CoilCoolingDXTwoSpeed.get
    cooling_coil.setRatedHighSpeedCOP(coil_cool_rated_high_speed_COP)
    cooling_coil.setRatedLowSpeedCOP(coil_cool_rated_low_speed_COP)
    case economizer_type
      when "No Economizer" :
      when "Fixed Dry Bulb Temperature Limit" :
        outdoor_air_system = hvac.supplyComponents(OpenStudio::Model::AirLoopHVACOutdoorAirSystem::iddObjectType())[0]
        outdoor_air_system = outdoor_air_system.to_AirLoopHVACOutdoorAirSystem.get
        outdoor_air_controller = outdoor_air_system.getControllerOutdoorAir 
        outdoor_air_controller = outdoor_air_controller.to_ControllerOutdoorAir.get
        outdoor_air_controller.setEconomizerControlType("FixedDryBulb")
        outdoor_air_controller.setEconomizerMaximumLimitDryBulbTemperature(economizer_dry_bulb_temp_limit)
      else
        puts "#{economizer_type} is not a valid selection for economizer type."
    end
  end
  def add_constructions(params)
    construction_library_path = params["construction_library_path"]
    degree_to_north = params["degree_to_north"]
    if not construction_library_path
      return false
    end
    construction_library_path = OpenStudio::Path.new(construction_library_path)
    if OpenStudio::exists(construction_library_path)
      construction_library = OpenStudio::IdfFile::load(construction_library_path, "OpenStudio".to_IddFileType).get
    else
      puts "#{construction_library_path} couldn't be found"
    end
    self.addObjects(construction_library.objects)
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
    versionTranslator = OpenStudio::OSVersion::VersionTranslator.new
    component = OpenStudio::LocalBCL::instance().getOnDemandComponent(generator)
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
  def add_densities()
    ventilation=self.getDesignSpecificationOutdoorAirs 
    ventilation.first.setOutdoorAirFlowAirChangesperHour(1.0)      
  end
  def save_openstudio_osm(params)
    osm_save_directory = params["osm_save_directory"]
    osm_name = params["osm_name"]
    save_path = OpenStudio::Path.new("#{osm_save_directory}/#{osm_name}")
    self.save(save_path,true)
  end
  def translate_to_energyplus_and_save_idf(params)
    idf_save_directory = params["idf_save_directory"]
    idf_name = params["idf_name"]
    forward_translator = OpenStudio::EnergyPlus::ForwardTranslator.new()
    workspace = forward_translator.translateModel(self)
    idf_save_path = OpenStudio::Path.new("#{idf_save_directory}/#{idf_name}")
    workspace.save(idf_save_path,true)
  end
  def add_design_days(params)
    require 'openstudio/energyplus/find_energyplus'
	loc_filename = params["loc_filename"]
    ep_hash = OpenStudio::EnergyPlus::find_energyplus(8,0) 	
    weather_path = OpenStudio::Path.new(ep_hash[:energyplus_weatherdata].to_s)
    ddy_path = OpenStudio::Path.new("#{weather_path}/#{loc_filename}.idf") #sometimes ddy, OpenStudio error Kyle is fixing 
    if OpenStudio::exists(ddy_path)
      ddy_idf = OpenStudio::IdfFile::load(ddy_path, "EnergyPlus".to_IddFileType).get
      ddy_workspace = OpenStudio::Workspace.new(ddy_idf)
      reverse_translator = OpenStudio::EnergyPlus::ReverseTranslator.new()
      ddy_model = reverse_translator.translateWorkspace(ddy_workspace)
      stringent_sizing_criteria = params["stringent_sizing_criteria"]
      if stringent_sizing_criteria == "yes"
        objects_new = ddy_model.objects.dup
        objects_new.delete_if {|x| x.to_s.include?"99%"}
        objects_new.delete_if {|x| x.to_s.include?"2%"}
        objects_new.delete_if {|x| x.to_s.include?"1%"}
        puts objects_new
        self.addObjects(objects_new)
      elsif stringent_sizing_criteria == "no"
        puts ddy_model.objects
        self.addObjects(ddy_model.objects)
      end
    else
      puts "#{ddy_path} couldn't be found"
    end 
  end
  def self.run_energyplus_simulation(params)
    require 'openstudio/energyplus/find_energyplus'
    idf_directory = params["idf_directory"]
	epw_filename = params['epw_filename']
    idf_name = params["idf_name"]
    ep_hash = OpenStudio::EnergyPlus::find_energyplus(8,0) 
    weather_path = OpenStudio::Path.new(ep_hash[:energyplus_weatherdata].to_s)
    puts epw_path = OpenStudio::Path.new("#{weather_path.to_s}/#{epw_filename}.epw")
    run_manager_db_path = OpenStudio::Path.new("#{idf_directory}/VirtualPULSE.db")
    run_manager = OpenStudio::Runmanager::RunManager.new(run_manager_db_path, true)
    config_options = run_manager.getConfigOptions()
    config_options.fastFindEnergyPlus()
    tools = config_options.getTools()
    sys_num_array = Array.new
    idf_path = OpenStudio::Path.new("#{idf_directory}/#{idf_name}")
    output_path = OpenStudio::Path.new("#{idf_directory}/ENERGYPLUS/#{idf_name}")
    output_path_string = File.dirname(output_path.to_s)
    workflow = OpenStudio::Runmanager::Workflow.new("EnergyPlusPreProcess->EnergyPlus")
    workflow.add(tools)
    job = workflow.create(output_path, idf_path, epw_path)
    run_manager.enqueue(job, true)
    while run_manager.workPending()
      sleep 1
      OpenStudio::Application::instance().processEvents()
    end
  end  
end #end class

#-Set_Rectangle-Shape.rb------------------------------------------------------------------------#
class SetRectangleShapeFloorPlan < OpenStudio::Ruleset::ModelUserScript
  def name
    return "Set Rectangle-Shape Floor Plan"
  end
  def add_geometry_rectangle(model, params)
    length = params["length"]
    width = params["width"]
    num_floors = params["num_floors"]
    floor_to_floor_height = params["floor_to_floor_height"]
    plenum_height = params["plenum_height"]
    perimeter_zone_depth = params["perimeter_zone_depth"]
    shortest_side = [length,width].min
    if perimeter_zone_depth < 0 or 2*perimeter_zone_depth >= (shortest_side - 1e-4)
	  puts "perimeter_zone_depth error."
      return false
    end
    for floor in (0..num_floors-1)
      z = floor_to_floor_height * floor
      story = OpenStudio::Model::BuildingStory.new(model)
      story.setNominalFloortoFloorHeight(floor_to_floor_height)
      story.setName("Story #{floor+1}")
      nw_point = OpenStudio::Point3d.new(0,width,z)
      ne_point = OpenStudio::Point3d.new(length,width,z)
      se_point = OpenStudio::Point3d.new(length,0,z)
      sw_point = OpenStudio::Point3d.new(0,0,z)
      m = OpenStudio::Matrix.new(4,4,0)
        m[0,0] = 1
        m[1,1] = 1
        m[2,2] = 1
        m[3,3] = 1
      if perimeter_zone_depth > 0
        perimeter_nw_point = nw_point + OpenStudio::Vector3d.new(perimeter_zone_depth,-perimeter_zone_depth,0)
        perimeter_ne_point = ne_point + OpenStudio::Vector3d.new(-perimeter_zone_depth,-perimeter_zone_depth,0)
        perimeter_se_point = se_point + OpenStudio::Vector3d.new(-perimeter_zone_depth,perimeter_zone_depth,0)
        perimeter_sw_point = sw_point + OpenStudio::Vector3d.new(perimeter_zone_depth,perimeter_zone_depth,0)
        west_polygon = OpenStudio::Point3dVector.new
          west_polygon << sw_point
          west_polygon << nw_point
          west_polygon << perimeter_nw_point
          west_polygon << perimeter_sw_point
        west_space = OpenStudio::Model::Space::fromFloorPrint(west_polygon, floor_to_floor_height, model)
        west_space = west_space.get
        m[0,3] = sw_point.x
        m[1,3] = sw_point.y
        m[2,3] = sw_point.z
        west_space.changeTransformation(OpenStudio::Transformation.new(m))
        west_space.setBuildingStory(story)
        west_space.setName("Story #{floor+1} West Perimeter Space")
        north_polygon = OpenStudio::Point3dVector.new
          north_polygon << nw_point
          north_polygon << ne_point
          north_polygon << perimeter_ne_point
          north_polygon << perimeter_nw_point
        north_space = OpenStudio::Model::Space::fromFloorPrint(north_polygon, floor_to_floor_height, model)
        north_space = north_space.get
        m[0,3] = perimeter_nw_point.x
        m[1,3] = perimeter_nw_point.y
        m[2,3] = perimeter_nw_point.z
        north_space.changeTransformation(OpenStudio::Transformation.new(m))
        north_space.setBuildingStory(story)
        north_space.setName("Story #{floor+1} North Perimeter Space")
        east_polygon = OpenStudio::Point3dVector.new
          east_polygon << ne_point
          east_polygon << se_point
          east_polygon << perimeter_se_point
          east_polygon << perimeter_ne_point
        east_space = OpenStudio::Model::Space::fromFloorPrint(east_polygon, floor_to_floor_height, model)
        east_space = east_space.get
        m[0,3] = perimeter_se_point.x
        m[1,3] = perimeter_se_point.y
        m[2,3] = perimeter_se_point.z
        east_space.changeTransformation(OpenStudio::Transformation.new(m))
        east_space.setBuildingStory(story)
        east_space.setName("Story #{floor+1} East Perimeter Space")
        south_polygon = OpenStudio::Point3dVector.new
          south_polygon << se_point
          south_polygon << sw_point
          south_polygon << perimeter_sw_point
          south_polygon << perimeter_se_point
        south_space = OpenStudio::Model::Space::fromFloorPrint(south_polygon, floor_to_floor_height, model)
        south_space = south_space.get
        m[0,3] = sw_point.x
        m[1,3] = sw_point.y
        m[2,3] = sw_point.z
        south_space.changeTransformation(OpenStudio::Transformation.new(m))
        south_space.setBuildingStory(story)
        south_space.setName("Story #{floor+1} South Perimeter Space")
        core_polygon = OpenStudio::Point3dVector.new
          core_polygon << perimeter_sw_point
          core_polygon << perimeter_nw_point
          core_polygon << perimeter_ne_point
          core_polygon << perimeter_se_point
        core_space = OpenStudio::Model::Space::fromFloorPrint(core_polygon, floor_to_floor_height, model)
        core_space = core_space.get
        m[0,3] = perimeter_sw_point.x
        m[1,3] = perimeter_sw_point.y
        m[2,3] = perimeter_sw_point.z
        core_space.changeTransformation(OpenStudio::Transformation.new(m))
        core_space.setBuildingStory(story)
        core_space.setName("Story #{floor+1} Core Space")
      else
        core_polygon = OpenStudio::Point3dVector.new
          core_polygon << sw_point
          core_polygon << nw_point
          core_polygon << ne_point
          core_polygon << se_point
        core_space = OpenStudio::Model::Space::fromFloorPrint(core_polygon, floor_to_floor_height, model)
        core_space = core_space.get
        m[0,3] = sw_point.x
        m[1,3] = sw_point.y
        m[2,3] = sw_point.z
        core_space.changeTransformation(OpenStudio::Transformation.new(m))
        core_space.setBuildingStory(story)
        core_space.setName("Story #{floor+1} Core Space")
      end
      story.setNominalZCoordinate(z)
    end #End of floor loop
  end
end