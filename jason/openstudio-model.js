
var openstudio = require("OpenStudio").openstudio;
var sys = require('sys');
var exec = require('child_process').exec;


function OpenStudioModel(buildingData, runmanager) {
  this.model = new openstudio.model.Model();
  this.runManager = runmanager;

  console.log("Constructed openstudio.model.Model()");

  this.add_geometry_rectangle = function(architecture) {
    var length = architecture.building_length;
    var width = architecture.building_width;
    var num_floors = architecture.number_of_floors;
    var floor_to_floor_height = architecture.floor_to_floor_height;
    console.log("Floor to floor height " + floor_to_floor_height);
    var plenum_height = architecture.plenum_height;
    var perimeter_zone_depth = architecture.perimeter_zone_depth;
    var shortest_side = Math.min(length,width);
    if (perimeter_zone_depth < 0 || 2*perimeter_zone_depth >= (shortest_side - 1e-4)) {
      console.log("perimeter_zone_depth error.");
      return false;
    }

    for (var floor = 0; floor < num_floors; ++floor) {
      var z = floor_to_floor_height * floor;
      var story = new openstudio.model.BuildingStory(this.model);
      story.setNominalFloortoFloorHeight(floor_to_floor_height);
      story.setName("Story " + (floor + 1));
      var nw_point = new openstudio.Point3d(0,width,z);
      var ne_point = new openstudio.Point3d(length,width,z);
      var se_point = new openstudio.Point3d(length,0,z);
      var sw_point = new openstudio.Point3d(0,0,z);
      var m = new openstudio.Matrix(4,4,0);
      m.__setitem__(0,0,1);
      m.__setitem__(1,1,1);
      m.__setitem__(2,2,1);
      m.__setitem__(3,3,1);

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
        west_space = openstudio.model.Space.fromFloorPrint(west_polygon, floor_to_floor_height, this.model);
        west_space = west_space.get();
        m.__setitem__(0,3,sw_point.x());
        m.__setitem__(1,3,sw_point.y());
        m.__setitem__(2,3,sw_point.z());
        west_space.changeTransformation(new openstudio.Transformation(m));
        west_space.setBuildingStory(story);
        west_space.setName("Story " + (floor+1) + " West Perimeter Space");
        var north_polygon = new openstudio.Point3dVector();
        north_polygon.add(nw_point);
        north_polygon.add(ne_point);
        north_polygon.add(perimeter_ne_point);
        north_polygon.add(perimeter_nw_point);
        var north_space = openstudio.model.Space.fromFloorPrint(north_polygon, floor_to_floor_height, this.model).get();
        m.__setitem__(0,3,perimeter_nw_point.x());
        m.__setitem__(1,3,perimeter_nw_point.y());
        m.__setitem__(2,3,perimeter_nw_point.z());
        north_space.changeTransformation(new openstudio.Transformation(m));
        north_space.setBuildingStory(story);
        north_space.setName("Story " + (floor+1) + " North Perimeter Space");
        var east_polygon = new openstudio.Point3dVector();
        east_polygon.add(ne_point);
        east_polygon.add(se_point);
        east_polygon.add(perimeter_se_point);
        east_polygon.add(perimeter_ne_point);
        var east_space = openstudio.model.Space.fromFloorPrint(east_polygon, floor_to_floor_height, this.model);
        east_space = east_space.get();
        m.__setitem__(0,3,perimeter_se_point.x());
        m.__setitem__(1,3,perimeter_se_point.y());
        m.__setitem__(2,3,perimeter_se_point.z());
        east_space.changeTransformation(new openstudio.Transformation(m));
        east_space.setBuildingStory(story);
        east_space.setName("Story " + (floor+1) +" East Perimeter Space");
        var south_polygon = new openstudio.Point3dVector();
        south_polygon.add(se_point);
        south_polygon.add(sw_point);
        south_polygon.add(perimeter_sw_point);
        south_polygon.add(perimeter_se_point);
        var south_space = openstudio.model.Space.fromFloorPrint(south_polygon, floor_to_floor_height, this.model);
        south_space = south_space.get();
        m.__setitem__(0,3,sw_point.x());
        m.__setitem__(1,3,sw_point.y());
        m.__setitem__(2,3,sw_point.z());
        south_space.changeTransformation(new openstudio.Transformation(m));
        south_space.setBuildingStory(story);
        south_space.setName("Story " + (floor+1) + " South Perimeter Space");
        var core_polygon = new openstudio.Point3dVector();
        core_polygon.add(perimeter_sw_point);
        core_polygon.add(perimeter_nw_point);
        core_polygon.add(perimeter_ne_point);
        core_polygon.add(perimeter_se_point);
        var core_space = openstudio.model.Space.fromFloorPrint(core_polygon, floor_to_floor_height, this.model);
        core_space = core_space.get();
        m.__setitem__(0,3,perimeter_sw_point.x());
        m.__setitem__(1,3,perimeter_sw_point.y());
        m.__setitem__(2,3,perimeter_sw_point.z());
        core_space.changeTransformation(new openstudio.Transformation(m));
        core_space.setBuildingStory(story);
        core_space.setName("Story " + (floor+1) + " Core Space");
      } else {
        var core_polygon = new openstudio.Point3dVector();
        core_polygon.add(sw_point);
        core_polygon.add(nw_point);
        core_polygon.add(ne_point);
        core_polygon.add(se_point);
        var core_space = openstudio.model.Space.fromFloorPrint(core_polygon, floor_to_floor_height, this.model);
        core_space = core_space.get()
        m.__setitem__(0,3,sw_point.x());
        m.__setitem__(1,3,sw_point.y());
        m.__setitem__(2,3,sw_point.z());
        core_space.changeTransformation(new openstudio.Transformation(m));
        core_space.setBuildingStory(story);
        core_space.setName("Story " + (floor+1) + " Core Space");
      }
      story.setNominalZCoordinate(z);
    } //End of floor loop
  } 

  this.add_geometry = function(architecture) {
    geo_type = architecture.footprint_shape;

    if (geo_type == 'Rectangle')
    {
      this.add_geometry_rectangle(architecture);
    } else {
      console.log("Geometry type not implemented yet.");
      return false;
    }

    var spaces = openstudio.model.getSpaces(this.model)
    openstudio.model.matchSurfaces(spaces) //Match surfaces and sub-surfaces within spaces

    for (var i = 0; i < spaces.size(); ++i)
    {
      var space = spaces.get(i);
      if (!space.thermalZone().is_initialized())
      {
        space.setThermalZone(new openstudio.model.ThermalZone(this.model));
      }
    } //end space loop
  }

  this.add_windows = function(architecture) {
    var wwr = architecture.window_to_wall_ratio;
    var offset = architecture.window_offset;
    var application_type = architecture.window_offset_application_type;
    heightOffsetFromFloor = false;
    if (application_type == "Above Floor") {
      heightOffsetFromFloor = true;
    } else {
      heightOffsetFromFloor = false;
    }

    var surfaces = openstudio.model.getSurfaces(this.model);

    for (var i = 0; i < surfaces.size(); ++i)
    {
      var s = surfaces.get(i);
      if (s.outsideBoundaryCondition() == "Outdoors") {
        /*new_window = */   s.setWindowToWallRatio(wwr, offset, heightOffsetFromFloor);
      }
    }
  }

  this.add_hvac = function(mechanical) {
    var fan_eff = mechanical.fan_efficiency;
    var boiler_eff = mechanical.boiler_efficiency;
    var boiler_fuel_type = mechanical.boiler_fuel_type;
    var coil_cool_rated_high_speed_COP = mechanical.coil_cool_rated_high_speed_COP;
    var coil_cool_rated_low_speed_COP = mechanical.coil_cool_rated_low_speed_COP;
    var economizer_type = mechanical.economizer_type;
    var economizer_dry_bulb_temp_limit = mechanical.economizer_dry_bulb_temp_limit;
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

    var plant_loop = openstudio.model.toCoilHeatingWater(heating_coil).get().plantLoop().get();

    var boiler = plant_loop.supplyComponents(new openstudio.IddObjectType("OS:Boiler:HotWater")).get(0);
    boiler = openstudio.model.toBoilerHotWater(boiler).get();
    boiler.setFuelType(boiler_fuel_type);
    boiler.setNominalThermalEfficiency(boiler_eff);

    var cooling_coil = hvac.supplyComponents(new openstudio.IddObjectType("OS:Coil:Cooling:DX:TwoSpeed")).get(0);
    cooling_coil = openstudio.model.toCoilCoolingDXTwoSpeed(cooling_coil).get();
    cooling_coil.setRatedHighSpeedCOP(coil_cool_rated_high_speed_COP);
    cooling_coil.setRatedLowSpeedCOP(coil_cool_rated_low_speed_COP);

    if (economizer_type == "No Economizer")
    {
    } else if (economizer_type == "Fixed Dry Bulb Temperature Limit") {
      var outdoor_air_system = hvac.supplyComponents(openstudio.model.AirLoopHVACOutdoorAirSystem.iddObjectType()).get(0);
      outdoor_air_system = openstudio.model.toAirLoopHVACOutdoorAirSystem(outdoor_air_system).get();
      var outdoor_air_controller = outdoor_air_system.getControllerOutdoorAir();
      outdoor_air_controller = openstudio.model.toControllerOutdoorAir(outdoor_air_controller).get();
      outdoor_air_controller.setEconomizerControlType("FixedDryBulb");
      outdoor_air_controller.setEconomizerMaximumLimitDryBulbTemperature(economizer_dry_bulb_temp_limit);
    } else {
      console.log(economizer_type + " is not a valid selection for economizer type.");
    }
  }

  this.add_constructions = function(constructions) {
    var construction_library_path = constructions.construction_library_path;
    var degree_to_north = constructions.degree_to_north;

    if (construction_library_path == undefined) {
      return false;
    }

    construction_library_path = new openstudio.path(construction_library_path);

    var construction_library = null;
    if (openstudio.exists(construction_library_path)) {
      construction_library = openstudio.IdfFile.load(construction_library_path, new openstudio.IddFileType("OpenStudio")).get();
    } else {
      console.log(construction_library_path + " couldn't be found");
      return false;
    }

    this.model.addObjects(construction_library.objects());
    var building = openstudio.model.getBuilding(this.model);
    var default_construction_set = openstudio.model.getDefaultConstructionSets(this.model).get(0);
    building.setDefaultConstructionSet(default_construction_set);
    building.setNorthAxis(degree_to_north);
  },

  this.add_space_type = function(space_type) {
    openstudio.Application.instance();
    var localblc = openstudio.LocalBCL.instance(new openstudio.path("/home/jason"));
    var bcl = new openstudio.RemoteBCL();
    bcl.setProdAuthKey("xsxYuim9hvuuGdVFvhM5GBxNLPnDmNgE");
    var nrel_reference_building_vintage = space_type.NREL_reference_building_vintage;
    var climate_zone = space_type.climate_zone;
    var nrel_reference_building_primary_space_type = space_type.NREL_reference_building_primary_space_type;
    var nrel_reference_building_secondary_space_type = space_type.NREL_reference_building_secondary_space_type;
    bcl.downloadOnDemandGenerator("bb8aa6a0-6a25-012f-9521-00ff10704b07");
    generator = bcl.waitForOnDemandGenerator();

    if (!generator.is_initialized()) {
      console.log("generator empty");
      return false;
    }

    generator = generator.get();
    generator.setArgumentValue("NREL_reference_building_vintage", nrel_reference_building_vintage);
    generator.setArgumentValue("Climate_zone", climate_zone);
    generator.setArgumentValue("NREL_reference_building_primary_space_type", nrel_reference_building_primary_space_type);
    generator.setArgumentValue("NREL_reference_building_secondary_space_type", nrel_reference_building_secondary_space_type);

    var versionTranslator = new openstudio.osversion.VersionTranslator();

    var component = openstudio.LocalBCL.instance().getOnDemandComponent(generator);
    if (!component.is_initialized()) {
      console.log("Space type not found locally, downloading");
      component = bcl.getOnDemandComponent(generator);
    }
    if (!component.is_initialized()) {
      console.log("component not downloaded");
      return false;
    }
    component = component.get();
    var oscFiles = component.files("osc");
    if (oscFiles.isEmpty()) {
      console.log("No osc files found");
      return false;
    }

    var oscPath = new openstudio.path(oscFiles.get(0));
    var modelComponent = versionTranslator.loadComponent(oscPath);
    if (!modelComponent.is_initialized()) {
      console.log("Could not load component");
      return false
    }
    modelComponent = modelComponent.get();
    this.model.insertComponent(modelComponent);
    space_type = openstudio.model.toSpaceType(this.model.getObjectsByType(new openstudio.IddObjectType("OS_SpaceType")).get(0)).get();
    openstudio.model.getBuilding(this.model).setSpaceType(space_type);
  }

  this.add_thermostats = function(mechanical) {
    var heating_setpoint = mechanical.heating_setpoint;
    var cooling_setpoint = mechanical.cooling_setpoint;
    var time_24hrs = new openstudio.Time(0,24,0,0);
    var cooling_sch = new openstudio.model.ScheduleRuleset(this.model);
    cooling_sch.setName("Cooling Sch");
    cooling_sch.defaultDaySchedule().setName("Cooling Sch Default");
    cooling_sch.defaultDaySchedule().addValue(time_24hrs,cooling_setpoint);
    var heating_sch = new openstudio.model.ScheduleRuleset(this.model);
    heating_sch.setName("Heating Sch");
    heating_sch.defaultDaySchedule().setName("Heating Sch Default");
    heating_sch.defaultDaySchedule().addValue(time_24hrs,heating_setpoint);
    var new_thermostat = new openstudio.model.ThermostatSetpointDualSetpoint(this.model);
    new_thermostat.setHeatingSchedule(heating_sch);
    new_thermostat.setCoolingSchedule(cooling_sch);

    var thermalzones = openstudio.model.getThermalZones(this.model);

    for (var i = 0; i < thermalzones.size(); ++i) {
      thermalzones.get(i).setThermostatSetpointDualSetpoint(new_thermostat)
    }
  }

  this.add_densities = function() {
    ventilation=openstudio.model.getDesignSpecificationOutdoorAirs(this.model);
    ventilation.get(0).setOutdoorAirFlowAirChangesperHour(1.0);
  }

  this.save_openstudio_osm = function(osm_save_directory, osm_name) {
    var save_path = new openstudio.path(osm_save_directory + "/" + osm_name);
    this.model.save(save_path,true);
  }

  this.translate_to_energyplus_and_save_idf = function(idf_save_directory, idf_name) {
    var forward_translator = new openstudio.energyplus.ForwardTranslator();
    var workspace = forward_translator.translateModel(this.model);
    var idf_save_path = new openstudio.path(idf_save_directory + "/" + idf_name);
    workspace.save(idf_save_path,true);
  }

  this.add_design_days = function(location, weather_path) {
    var loc_filename = location.location_filename;
    // now that the location is determined save the filename off for later consumption
    this.loc_filename = loc_filename;
    var ddy_path = new openstudio.path(weather_path + "/" + loc_filename + ".idf"); // sometimes ddy, OpenStudio error Kyle is fixing
    if (openstudio.exists(ddy_path)) {
      var ddy_idf = openstudio.IdfFile.load(ddy_path, new openstudio.IddFileType("EnergyPlus")).get();
      var ddy_workspace = new openstudio.Workspace(ddy_idf);
      var reverse_translator = new openstudio.energyplus.ReverseTranslator();
      var ddy_model = reverse_translator.translateWorkspace(ddy_workspace);
      var stringent_sizing_criteria = location.stringent_sizing_criteria;
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
       console.log(ddy_model.objects());
       this.model.addObjects(ddy_model.objects());
      }
    } else {
      console.log(openstudio.toString(ddy_path) + " couldn't be found");
    }
  }

  this.add_load_summary_report = function(idf_file) {
    function puts(error, stdout, stderr) { sys.puts(stdout); }
    exec("sed -i 's/\\(Output:Table:SummaryReports,\\)/\\1\\n  ZoneComponentLoadSummary,/g'  " + idf_file, puts);
  }

  //modify the default unit system to I-P unit in idf_file
  this.convert_unit_to_ip = function(idf_file) {
    function puts(error, stdout, stderr) { sys.puts(stdout); }
    exec("sed -i  's/\\(HTML;.*\\)/HTML,\\n  InchPound;/g'  " + idf_file, puts);
  }

  this.run_energyplus_simulation = function(idf_directory, idf_name) {
    var weather_path = this.runManager.getConfigOptions().getDefaultEPWLocation();
    var epw_path = new openstudio.path(openstudio.toString(weather_path) + "/" + this.loc_filename + ".epw");
    var tools = this.runManager.getConfigOptions().getTools();
    var idf_path = new openstudio.path(idf_directory + "/" + idf_name);
    var output_path = new openstudio.path(idf_directory + "/ENERGYPLUS/" + idf_name);
    var workflow = new openstudio.runmanager.Workflow("EnergyPlusPreProcess->EnergyPlus");
    workflow.add(tools);
    console.log("EPW path: " + openstudio.toString(epw_path) + " epw exists: " + openstudio.exists(epw_path));
   

    var job = workflow.create(output_path, idf_path, epw_path);
    
    this.runManager.enqueue(job, true);
    this.runManager.setPaused(false);

    this.runManager.waitForFinished();
    return job;
  }


  this.add_geometry(buildingData.building.architecture);
  this.add_windows(buildingData.building.architecture);
  this.add_hvac(buildingData.building.mechanical);
  this.add_thermostats(buildingData.building.mechanical);
  this.add_constructions(buildingData.building.constructions);
  this.add_space_type(buildingData.building.space_type);
  this.add_densities();
  this.add_design_days(buildingData.building.location, openstudio.toString(this.runManager.getConfigOptions().getDefaultEPWLocation()));


};


exports.OpenStudioModel = OpenStudioModel;


