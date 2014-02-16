
var openstudio = require("OpenStudio").openstudio;
var sys = require('sys');
var exec = require('child_process').exec;


function OpenstudioModel() {
  this.model = new openstudio.model.Model();
};

OpenstudioModel.prototype = {
  add_geometry: function(size_params, building_params) {
    geo_type = building_params['geo_type'];

    if (geo_type == 'Rectangle')
    {
      var shape_plan = new SetRectangleShapeFloorPlan();
      shape_plan.add_geometry_rectangle(model, size_params, building_params)
    } else {
      console.log("Geometry type not implemented yet.");
      return false;
    }

    var spaces = openstudio.model.getSpaces(model)
    openstudio.model.matchSurfaces(spaces) //Match surfaces and sub-surfaces within spaces

    for (var i = 0; i < spaces.size(); ++i)
    {
      var space = spaces.get(i);
      if (space.thermalZone().empty())
      {
        space.setThermalZone(new openstudio.model.ThermalZone(model));
      }
    } //end space loop
  },

  add_windows: function(params) {
    var wwr = params["wwr"];
    var offset = params["offset"];
    var application_type = params["application_type"];
    heightOffsetFromFloor = false;
    if (application_type == "Above Floor") {
      heightOffsetFromFloor = true;
    } else {
      heightOffsetFromFloor = false;
    }

    var surfaces = openstudio.model.getSurfaces(this.model);

    for (var i = 0; i < surfaces.size(); ++i)
    {
      if (s.outsideBoundaryCondition == "Outdoors") {
        /*new_window = */   s.setWindowToWallRatio(wwr, offset, heightOffsetFromFloor);
      }
    }
  },

  add_hvac: function(params) {
    var fan_eff = params["fan_eff"];
    var boiler_eff = params["boiler_eff"];
    var boiler_fuel_type = params["boiler_fuel_type"];
    var coil_cool_rated_high_speed_COP = params["coil_cool_rated_high_speed_COP"];
    var coil_cool_rated_low_speed_COP = params["coil_cool_rated_low_speed_COP"];
    var economizer_type = params["economizer_type"];
    var economizer_dry_bulb_temp_limit = params["economizer_dry_bulb_temp_limit"];
    var economizer_enthalpy_limit = params["economizer_enthalpy_limit"];
    var zones = openstudio.model.getThermalZones(this.model);
    var hvac = openstudio.model.addSystemType5(this.model);
    hvac = openstudio.model.toAirLoopHVAC(hvac).get();

    for (var i = 0; i < zones.size(); ++i)
    {
      hvac.addBranchForZone(zones.get(i));
    }
    var supply_fan = hvac.supplyComponents(new openstudio.IddObjectType("OS:Fan:VariableVolume")).get(0);
    supply_fan = openstudio.model.toFanVariableVolume(supply_fan).get();
    supply_fan.setFanEfficiency(fan_eff);

    var heating_coil = hvac.supplyComponents(new openstudio.IddObjectType("OS:Coil:Heating:Water")).get(0);

    var plant_loop = openstudio.model.toCoilHeatingWater(heating_coil).get().plantLoop.get();

    var boiler = plant_loop.supplyComponents(new openstudio.IddObjectType("OS:Boiler:HotWater")).get(0);
    boiler = openstudio.model.toBoilerHotWater(boiler).get();
    boiler.setFuelType(boiler_fuel_type);
    boiler.setNominalThermalEfficiency(boiler_eff);

    var cooling_coil = hvac.supplyComponents(new openstudio.IddObjectType("OS:Coil:Cooling:DX:TwoSpeed")).get(0);
    cooling_coil = openstudio.model.toCoilCoolingDXTwoSpeed(cooling_coil).get();
    cooling_coil.setRatedHighSpeedCOP(coil_cool_rated_high_speed_COP);
    cooling_coil.setRatedLowSpeedCOP(coil_cool_rated_low_speed_COP);

    if (economizer_type == "No Economizer"
        || economizer_type == "Fixed Dry Bulb Temperature Limit")
    {
      var outdoor_air_system = hvac.supplyComponents(openstudio.model.airloophvacoutdoorairsystem.iddObjectType()).get(0);
      outdoor_air_system = openstudio.model.toAirLoopHVACOutdoorAirSystem(outdoor_air_system).get();
      var outdoor_air_controller = outdoor_air_system.getControllerOutdoorAir();
      outdoor_air_controller = openstudio.model.toControllerOutdoorAir(outdoor_air_controller).get();
      outdoor_air_controller.setEconomizerControlType("FixedDryBulb");
      outdoor_air_controller.setEconomizerMaximumLimitDryBulbTemperature(economizer_dry_bulb_temp_limit);
    } else {
      console.log("#{economizer_type} is not a valid selection for economizer type.");
    }
  },

  add_constructions: function(params) {
    var construction_library_path = params["construction_library_path"];
    var degree_to_north = params["degree_to_north"];

    if (construction_library_path == undefined) {
      return false;
    }

    construction_library_path = new openstudio.Path(construction_library_path);

    var construction_library = null;
    if (openstudio.exists(construction_library_path)) {
      construction_library = openstudio.idffile.load(construction_library_path, "OpenStudio".to_IddFileType).get();
    } else {
      console.log("#{construction_library_path} couldn't be found");
      return false;
    }

    this.model.addObjects(construction_library.objects());
    var building = openstudio.model.getBuilding(this.model);
    var default_construction_set = openstudio.model.getDefaultConstructionSets(this.model).get(0);
    building.setDefaultConstructionSet(default_construction_set);
    building.setNorthAxis(degree_to_north);
  },

  add_space_type: function(params) {
    openstudio.application.instance();
    var localblc = openstudio.localbcl.instance(openstudio.Path.new("/home/virtualpulse"));
    var bcl = new openstudio.RemoteBCL();
    bcl.setProdAuthKey("xsxYuim9hvuuGdVFvhM5GBxNLPnDmNgE");
    var nrel_reference_building_vintage = params["NREL_reference_building_vintage"];
    var climate_zone = params["Climate_zone"];
    var nrel_reference_building_primary_space_type = params["NREL_reference_building_primary_space_type"];
    var nrel_reference_building_secondary_space_type = params["NREL_reference_building_secondary_space_type"];
    var remoteBCL = new openstudio.RemoteBCL();
    remoteBCL.downloadOnDemandGenerator("bb8aa6a0-6a25-012f-9521-00ff10704b07");
    generator = remoteBCL.waitForOnDemandGenerator();

    if (generator.empty()) {
      console.log("generator empty");
      return false;
    }

    generator = generator.get();
    generator.setArgumentValue("NREL_reference_building_vintage", nrel_reference_building_vintage);
    generator.setArgumentValue("Climate_zone", climate_zone);
    generator.setArgumentValue("NREL_reference_building_primary_space_type", nrel_reference_building_primary_space_type);
    generator.setArgumentValue("NREL_reference_building_secondary_space_type", nrel_reference_building_secondary_space_type);

    var versionTranslator = new openstudio.osversion.VersionTranslator();

    var component = openstudio.localbcl.instance().getOnDemandComponent(generator);
    if (component.empty()) {
      console.log("Space type not found locally, downloading");
      component = remoteBCL.getOnDemandComponent(generator);
    }
    if (component.empty()) {
      console.log("component not downloaded");
      return false;
    }
    component = component.get();
    var oscFiles = component.files("osc");
    if (oscFiles.empty()) {
      console.log("No osc files found");
      return false;
    }

    var oscPath = new openstudio.Path(oscFiles.get(0));
    var modelComponent = versionTranslator.loadComponent(oscPath);
    if (modelComponent.empty()) {
      console.log("Could not load component");
      return false
    }
    modelComponent = modelComponent.get();
    this.model.insertComponent(modelComponent);
    space_type = this.model.getObjectsByType(new openstudio.IddObjectType("OS_SpaceType")).get(0).to_SpaceType.get();
    this.model.getBuilding.setSpaceType(space_type);
  },

  add_thermostats: function(params) {
    var heating_setpoint = params["heating_setpoint"];
    var cooling_setpoint = params["cooling_setpoint"];
    var time_24hrs = new openstudio.Time(0,24,0,0);
    var cooling_sch = new openstudio.model.ScheduleRuleset(this.model);
    cooling_sch.setName("Cooling Sch");
    cooling_sch.defaultDaySchedule.setName("Cooling Sch Default");
    cooling_sch.defaultDaySchedule.addValue(time_24hrs,cooling_setpoint);
    var heating_sch = new openstudio.model.ScheduleRuleset(this.model);
    heating_sch.setName("Heating Sch");
    heating_sch.defaultDaySchedule.setName("Heating Sch Default");
    heating_sch.defaultDaySchedule.addValue(time_24hrs,heating_setpoint);
    var new_thermostat = new openstudio.model.ThermostatSetpointDualSetpoint(this.model);
    new_thermostat.setHeatingSchedule(heating_sch);
    new_thermostat.setCoolingSchedule(cooling_sch);

    var thermalzones = openstudio.model.getThermalZones(this.model);

    for (var i = 0; i < thermalzones.size(); ++i) {
      thermalzones.get(i).setThermostatSetpointDualSetpoint(new_thermostat)
    }
  },

  add_densities: function() {
    ventilation=openstudio.model.getDesignSpecificationOutdoorAirs(this.model);
    ventilation.first().setOutdoorAirFlowAirChangesperHour(1.0);
  },

  save_openstudio_osm: function(params) {
    var osm_save_directory = params["osm_save_directory"];
    var osm_name = params["osm_name"];
    var save_path = new openstudio.Path(osm_save_directory + "/" + osm_name);
    this.model.save(save_path,true);
  },

  translate_to_energyplus_and_save_idf: function(params) {
    var idf_save_directory = params["idf_save_directory"];
    var idf_name = params["idf_name"];
    var forward_translator = new openstudio.energyplus.ForwardTranslator();
    var workspace = forward_translator.translateModel(this.model);
    var idf_save_path = new openstudio.Path(idf_save_directory + "/" + idf_name);
    workspace.save(idf_save_path,true);
  },

  add_design_days: function(params, weather_path) {
    var loc_filename = params["loc_filename"];
    var ddy_path = new openstudio.Path(weather_path + "/" + loc_filename + ".idf"); // sometimes ddy, OpenStudio error Kyle is fixing
    if (openstudio.exists(ddy_path)) {
      var ddy_idf = openstudio.idffile.load(ddy_path, openstudio.IddFileType.new("EnergyPlus")).get();
      var ddy_workspace = new openstudio.Workspace(ddy_idf);
      var reverse_translator = new openstudio.energyplus.ReverseTranslator();
      var ddy_model = reverse_translator.translateWorkspace(ddy_workspace);
      var stringent_sizing_criteria = params["stringent_sizing_criteria"];
      var objects = ddy_model.objects();
      if (stringent_sizing_criteria == "yes") {
        var objects_new = new openstudio.model.ModelObjectVector();
        for (var i = 0; i < objects.size(); ++i)
        {
          if (objects.get(i).toString().search("99%") != -1
              && objects.get(i).toString().search("2%") != -1
              && objects.get(i).toString().search("1%") != -1)
          {
            objects_new.add(objects.get(i));
          }
        }
        this.model.addObjects(objects_new);
      } else if (stringent_sizing_criteria == "no") {
       console.log(ddy_model.objects);
       this.model.addObjects(ddy_model.objects);
      }
    } else {
      console.log(ddy_path + " couldn't be found");
    }
  },

  add_load_summary_report: function(idf_file) {
    function puts(error, stdout, stderr) { sys.puts(stdout); }
    exec("sed -i 's/\\(Output:Table:SummaryReports,\\)/\\1\\n  ZoneComponentLoadSummary,/g'  " + idf_file, puts);
  },

  //modify the default unit system to I-P unit in idf_file
  convert_unit_to_ip: function(idf_file) {
    function puts(error, stdout, stderr) { sys.puts(stdout); }
    exec("sed  's/\\(HTML;.*\\)/HTML,\\n  InchPound;/g'  " + idf_file, puts);
  },

  run_energyplus_simulation: function(params) {
    /*
    idf_directory = params["idf_directory"]
    epw_filename = params['epw_filename']
    idf_name = params["idf_name"]
    ep_hash = openstudio.energyplus.find_energyplus(8,0)
    weather_path = new openstudio.Path(ep_hash[:energyplus_weatherdata].toString)
    console.log(epw_path = new openstudio.Path("#{weather_path.toString}/#{epw_filename}.epw"));
    run_manager_db_path = new openstudio.Path("#{idf_directory}/VirtualPULSE.db")
    run_manager = new openstudio.runmanager.RunManager(run_manager_db_path, true)
    config_options = run_manager.getConfigOptions()
    config_options.fastFindEnergyPlus()
    tools = config_options.getTools()
    sys_num_array = new Array()
    idf_path = new openstudio.Path("#{idf_directory}/#{idf_name}")
    output_path = new openstudio.Path("#{idf_directory}/ENERGYPLUS/#{idf_name}")
    output_path_string = File.dirname(output_path.toString)
    workflow = new openstudio.runmanager.Workflow("EnergyPlusPreProcess->EnergyPlus")
    workflow.add(tools)
    job = workflow.create(output_path, idf_path, epw_path)
    run_manager.enqueue(job, true)
    while run_manager.workPending()
      sleep 1
      openstudio.application.instance().processEvents()
    end
    */
  },

  add_geometry_rectangle: function(params) {
    var length = params["length"];
    var width = params["width"];
    var num_floors = params["num_floors"];
    var floor_to_floor_height = params["floor_to_floor_height"];
    var plenum_height = params["plenum_height"];
    var perimeter_zone_depth = params["perimeter_zone_depth"];
    var shortest_side = Math.min(length,width);
    if (perimeter_zone_depth < 0 || 2*perimeter_zone_depth >= (shortest_side - 1e-4)) {
      console.log("perimeter_zone_depth error.");
      return false;
    }

    for (var floor = 0; floor < num_floors; ++i) {
      var z = floor_to_floor_height * floor;
      var story = new openstudio.model.BuildingStory(model);
      story.setNominalFloortoFloorHeight(floor_to_floor_height);
      story.setName("Story " + (floor + 1));
      var nw_point = new openstudio.Point3d(0,width,z);
      var ne_point = new openstudio.Point3d(length,width,z);
      var se_point = new openstudio.Point3d(length,0,z);
      var sw_point = new openstudio.Point3d(0,0,z);
      var m = new openstudio.Matrix(4,4,0);
      m.setitem(0,0,1);
      m.setitem(1,1,1);
      m.setitem(2,2,1);
      m.setitem(3,3,1);

      if (perimeter_zone_depth > 0) {
        var perimeter_nw_point = nw_point.Plus(new openstudio.Vector3d(perimeter_zone_depth,-perimeter_zone_depth,0));
        var perimeter_ne_point = ne_point.Plus(new openstudio.Vector3d(-perimeter_zone_depth,-perimeter_zone_depth,0));
        var perimeter_se_point = se_point.Plus(new openstudio.Vector3d(-perimeter_zone_depth,perimeter_zone_depth,0));
        var perimeter_sw_point = sw_point.Plus(new openstudio.Vector3d(perimeter_zone_depth,perimeter_zone_depth,0));
        var west_polygon = new openstudio.Point3dVector();
        west_polygon.add(sw_point);
        west_polygon.add(nw_point);
        west_polygon.add(perimeter_nw_point);
        west_polygon.add(perimeter_sw_point);
        west_space = openstudio.model.space.fromFloorPrint(west_polygon, floor_to_floor_height, model);
        west_space = west_space.get();
        m.setitem(0,3,sw_point.x());
        m.setitem(1,3,sw_point.y());
        m.setitem(2,3,sw_point.z());
        west_space.changeTransformation(new openstudio.Transformation(m));
        west_space.setBuildingStory(story);
        west_space.setName("Story " + (floor+1) + " West Perimeter Space");
        var north_polygon = new openstudio.Point3dVector();
        north_polygon.add(nw_point);
        north_polygon.add(ne_point);
        north_polygon.add(perimeter_ne_point);
        north_polygon.add(perimeter_nw_point);
        var north_space = openstudio.model.space.fromFloorPrint(north_polygon, floor_to_floor_height, model).get();
        m.setitem(0,3,perimeter_nw_point.x());
        m.setitem(1,3,perimeter_nw_point.y());
        m.setitem(2,3,perimeter_nw_point.z());
        north_space.changeTransformation(new openstudio.Transformation(m));
        north_space.setBuildingStory(story);
        north_space.setName("Story " + (floor+1) + " North Perimeter Space");
        var east_polygon = new openstudio.Point3dVector();
        east_polygon.add(ne_point);
        east_polygon.add(se_point);
        east_polygon.add(perimeter_se_point);
        east_polygon.add(perimeter_ne_point);
        var east_space = openstudio.model.space.fromFloorPrint(east_polygon, floor_to_floor_height, model);
        east_space = east_space.get();
        m.setitem(0,3,perimeter_se_point.x());
        m.setitem(1,3,perimeter_se_point.y());
        m.setitem(2,3,perimeter_se_point.z());
        east_space.changeTransformation(new openstudio.Transformation(m));
        east_space.setBuildingStory(story);
        east_space.setName("Story " + (floor+1) +" East Perimeter Space");
        var south_polygon = new openstudio.Point3dVector();
        south_polygon.add(se_point);
        south_polygon.add(sw_point);
        south_polygon.add(perimeter_sw_point);
        south_polygon.add(perimeter_se_point);
        var south_space = openstudio.model.space.fromFloorPrint(south_polygon, floor_to_floor_height, model);
        south_space = south_space.get();
        m.setitem(0,3,sw_point.x());
        m.setitem(1,3,sw_point.y());
        m.setitem(2,3,sw_point.z());
        south_space.changeTransformation(new openstudio.Transformation(m));
        south_space.setBuildingStory(story);
        south_space.setName("Story " + (floor+1) + " South Perimeter Space");
        var core_polygon = new openstudio.Point3dVector();
        core_polygon.add(perimeter_sw_point);
        core_polygon.add(perimeter_nw_point);
        core_polygon.add(perimeter_ne_point);
        core_polygon.add(perimeter_se_point);
        var core_space = openstudio.model.space.fromFloorPrint(core_polygon, floor_to_floor_height, model);
        core_space = core_space.get();
        m.setitem(0,3,perimeter_sw_point.x());
        m.setitem(1,3,perimeter_sw_point.y());
        m.setitem(2,3,perimeter_sw_point.z());
        core_space.changeTransformation(new openstudio.Transformation(m));
        core_space.setBuildingStory(story);
        core_space.setName("Story " + (floor+1) + " Core Space");
      } else {
        var core_polygon = new openstudio.Point3dVector();
        core_polygon.add(sw_point);
        core_polygon.add(nw_point);
        core_polygon.add(ne_point);
        core_polygon.add(se_point);
        var core_space = openstudio.model.space.fromFloorPrint(core_polygon, floor_to_floor_height, model);
        core_space = core_space.get()
        m.setitem(0,3,sw_point.x());
        m.setitem(1,3,sw_point.y());
        m.setitem(2,3,sw_point.z());
        core_space.changeTransformation(new openstudio.Transformation(m));
        core_space.setBuildingStory(story);
        core_space.setName("Story " + (floor+1) + " Core Space");
      }
      story.setNominalZCoordinate(z);
    } //End of floor loop
  } 
};


