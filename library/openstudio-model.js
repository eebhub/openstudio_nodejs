
var openstudio = require("OpenStudio").openstudio;
var sys = require('sys');
var exec = require('child_process').exec;


function OpenStudioModel(building, runmanager) {
  this.model = new openstudio.model.Model();
  this.runManager = runmanager;

  console.log("Constructed openstudio.model.Model()");

  this.add_geometry_rectangle = function(architecture) {
    var length = architecture.buildingLength;
    var width = architecture.buildingWidth;
    var num_floors = architecture.numberOfFloors;
    var floor_to_floor_height = architecture.floorToFloorHeight;
    console.log("Floor to floor height " + floor_to_floor_height);
    var plenum_height = architecture.plenumHeight;
    var perimeter_zone_depth = architecture.perimeterZoneDepth;
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
        var west_space = openstudio.model.Space.fromFloorPrint(west_polygon, floor_to_floor_height, this.model);
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
        core_polygon = new openstudio.Point3dVector();
        core_polygon.add(sw_point);
        core_polygon.add(nw_point);
        core_polygon.add(ne_point);
        core_polygon.add(se_point);
        core_space = openstudio.model.Space.fromFloorPrint(core_polygon, floor_to_floor_height, this.model);
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
    var footprintShape = architecture.footprintShape;

    if (footprintShape == 'Rectangle')
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
    var windowToWallRatio = architecture.windowToWallRatio;
    var windowOffset = architecture.windowOffset;
    var application_type = architecture.windowOffsetApplicationType;
    
    var heightOffsetFromFloor = false;
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
        /*new_window = */   s.setWindowToWallRatio(windowToWallRatio, windowOffset, heightOffsetFromFloor);
      }
    }
  }

  this.add_hvac = function(mechanical) {
    var fanEfficiency = mechanical.fanEfficiency;
    var boilerEfficiency = mechanical.boilerEfficiency;
    var boilerFuelType = mechanical.boilerFuelType;
    var coilCoolRatedHighSpeedCOP = mechanical.coilCoolRatedHighSpeedCOP;
    var coilCoolRatedLowSpeedCOP = mechanical.coilCoolRatedLowSpeedCOP;
    var economizerType = mechanical.economizerType;
    var economizerDryBulbTempLimit = mechanical.economizerDryBulbTempLimit;
    var zones = openstudio.model.getThermalZones(this.model);
    var hvac = openstudio.model.addSystemType5(this.model);
    hvac = openstudio.model.toAirLoopHVAC(hvac).get();

    for (var i = 0; i < zones.size(); ++i)
    {
      hvac.addBranchForZone(zones.get(i));
    }
    var supplyFan = hvac.supplyComponents(new openstudio.IddObjectType("OS:Fan:VariableVolume")).get(0);
    supplyFan = openstudio.model.toFanVariableVolume(supplyFan).get();
    supplyFan.setFanEfficiency(fanEfficiency);

    var heatingCoil = hvac.supplyComponents(new openstudio.IddObjectType("OS:Coil:Heating:Water")).get(0);

    var plantLoop = openstudio.model.toCoilHeatingWater(heatingCoil).get().plantLoop().get();

    var boiler = plantLoop.supplyComponents(new openstudio.IddObjectType("OS:Boiler:HotWater")).get(0);
    boiler = openstudio.model.toBoilerHotWater(boiler).get();
    boiler.setFuelType(boilerFuelType);
    boiler.setNominalThermalEfficiency(boilerEfficiency);

    var coolingCoil = hvac.supplyComponents(new openstudio.IddObjectType("OS:Coil:Cooling:DX:TwoSpeed")).get(0);
    coolingCoil = openstudio.model.toCoilCoolingDXTwoSpeed(coolingCoil).get();
    coolingCoil.setRatedHighSpeedCOP(coilCoolRatedHighSpeedCOP);
    coolingCoil.setRatedLowSpeedCOP(coilCoolRatedLowSpeedCOP);

    if (economizerType == "No Economizer")
    {
    } else if (economizerType == "Fixed Dry Bulb Temperature Limit") {
      var outdoorAirSystem = hvac.supplyComponents(openstudio.model.AirLoopHVACOutdoorAirSystem.iddObjectType()).get(0);
      outdoorAirSystem = openstudio.model.toAirLoopHVACOutdoorAirSystem(outdoorAirSystem).get();
      var outdoorAirController = outdoorAirSystem.getControllerOutdoorAir();
      outdoorAirController = openstudio.model.toControllerOutdoorAir(outdoorAirController).get();
      outdoorAirController.setEconomizerControlType("FixedDryBulb");
      outdoorAirController.setEconomizerMaximumLimitDryBulbTemperature(economizerDryBulbTempLimit);
    } else {
      console.log(economizerType + " is not a valid selection for economizer type.");
    }
  }

  this.add_constructions = function(architecture, construction) {
    var degreeToNorth = architecture.degreeToNorth;
    var constructionLibraryPath = construction.constructionLibraryPath;

    if (constructionLibraryPath == undefined) {
      return false;
    }

    constructionLibraryPath = new openstudio.path(constructionLibraryPath);

    var constructionLibrary = null;
    if (openstudio.exists(constructionLibraryPath)) {
      constructionLibrary = openstudio.IdfFile.load(constructionLibraryPath, new openstudio.IddFileType("OpenStudio")).get();
    } else {
      console.log(constructionLibraryPath + " couldn't be found");
      return false;
    }

    this.model.addObjects(constructionLibrary.objects());
    var building = openstudio.model.getBuilding(this.model);
    var defaultConstructionSet = openstudio.model.getDefaultConstructionSets(this.model).get(0);
    building.setDefaultConstructionSet(defaultConstructionSet);
    building.setNorthAxis(degreeToNorth);
  },

  this.add_space_type = function(buildingInfo, site, paths) {
    var spaceType = buildingInfo;
    openstudio.Application.instance();
    var localblc = openstudio.LocalBCL.instance(new openstudio.path(paths.buildingComponentLibraryPath));
    var bcl = new openstudio.RemoteBCL();
    bcl.setProdAuthKey("xsxYuim9hvuuGdVFvhM5GBxNLPnDmNgE");
    var ASHRAEStandard = buildingInfo.ASHRAEStandard;
    var climateZone = site.climateZone;
    var activityType = buildingInfo.activityType;
    var activityTypeSecondary = buildingInfo.activityTypeSecondary;
    bcl.downloadOnDemandGenerator("bb8aa6a0-6a25-012f-9521-00ff10704b07");
    var generator = bcl.waitForOnDemandGenerator();

    if (!generator.is_initialized()) {
      console.log("generator empty");
      return false;
    }

    generator = generator.get();
    generator.setArgumentValue("NREL_reference_building_vintage", ASHRAEStandard);
    generator.setArgumentValue("Climate_zone", climateZone);
    generator.setArgumentValue("NREL_reference_building_primary_space_type", activityType);
    generator.setArgumentValue("NREL_reference_building_secondary_space_type", activityTypeSecondary);

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
      return false;
    }
    modelComponent = modelComponent.get();
    this.model.insertComponent(modelComponent);
    spaceType = openstudio.model.toSpaceType(this.model.getObjectsByType(new openstudio.IddObjectType("OS_SpaceType")).get(0)).get();
    openstudio.model.getBuilding(this.model).setSpaceType(spaceType);
  }

  this.add_thermostats = function(mechanical) {
    var heatingSetpoint = mechanical.heatingSetpoint;
    var coolingSetpoint = mechanical.coolingSetpoint;
    var time24hrs = new openstudio.Time(0,24,0,0);
    var coolingSchedule = new openstudio.model.ScheduleRuleset(this.model);
    coolingSchedule.setName("Cooling Sch");
    coolingSchedule.defaultDaySchedule().setName("Cooling Sch Default");
    coolingSchedule.defaultDaySchedule().addValue(time24hrs,coolingSetpoint);
    var heatingSchedule = new openstudio.model.ScheduleRuleset(this.model);
    heatingSchedule.setName("Heating Sch");
    heatingSchedule.defaultDaySchedule().setName("Heating Sch Default");
    heatingSchedule.defaultDaySchedule().addValue(time24hrs,heatingSetpoint);
    var newThermostat = new openstudio.model.ThermostatSetpointDualSetpoint(this.model);
    newThermostat.setHeatingSchedule(heatingSchedule);
    newThermostat.setCoolingSchedule(coolingSchedule);

    var thermalzones = openstudio.model.getThermalZones(this.model);

    for (var i = 0; i < thermalzones.size(); ++i) {
      thermalzones.get(i).setThermostatSetpointDualSetpoint(newThermostat)
    }
  }

  this.add_densities = function() {
    var ventilation=openstudio.model.getDesignSpecificationOutdoorAirs(this.model);
    ventilation.get(0).setOutdoorAirFlowAirChangesperHour(1.0);
  }

  this.save_openstudio_osm = function(outputPath, osmName) {
    var osmPath = new openstudio.path(outputPath + "/" + osmName);
    this.model.save(osmPath,true);
  }

  this.translate_to_energyplus_and_save_idf = function(outputPath, idfName) {
    var forwardTranslator = new openstudio.energyplus.ForwardTranslator();
    var workspace = forwardTranslator.translateModel(this.model);
    var idfPath = new openstudio.path(outputPath + "/" + idfName);
    workspace.save(idfPath,true);
  }

  this.add_design_days = function(site, weatherPath) {
    var weather = site.weather;
    // now that the location is determined save the filename off for later consumption
    this.weather = weather;
    var ddyPath = new openstudio.path(weatherPath + "/" + weather + ".ddy"); // sometimes ddy, OpenStudio error Kyle is fixing
    if (openstudio.exists(ddyPath)) {
      var ddy_idf = openstudio.IdfFile.load(ddyPath, new openstudio.IddFileType("EnergyPlus")).get();
      var ddyWorkspace = new openstudio.Workspace(ddy_idf);
      var reverseTranslator = new openstudio.energyplus.ReverseTranslator();
      var ddyModel = reverseTranslator.translateWorkspace(ddyWorkspace);
      var strictDesignDay = site.strictDesignDay;
      var objects = ddyModel.objects();
      if (strictDesignDay == "yes") {
        var objects_new = new openstudio.IdfObjectVector();
        for (var i = 0; i < objects.size(); ++i)
        {
          console.log("ddy_object: " + objects.get(i).briefDescription());
          if (objects.get(i).briefDescription().search("99%") == -1
              && objects.get(i).briefDescription().search("2%") == -1
              && objects.get(i).briefDescription().search("1%") == -1)
          {
            console.log("kept ddy_object: " + objects.get(i).briefDescription());
            objects_new.add(objects.get(i));
          }
        }
        this.model.addObjects(objects_new);
      } else if (strictDesignDay == "no") {
       console.log(ddyModel.objects());
       this.model.addObjects(ddyModel.objects());
      }
    } else {
      console.log(openstudio.toString(ddyPath) + " couldn't be found");
    }
  }

  this.add_load_summary_report = function(idfFile) {
    function puts(error, stdout, stderr) { sys.puts(stdout); }
    exec("sed -i 's/\\(Output:Table:SummaryReports,\\)/\\1\\n  ZoneComponentLoadSummary,/g'  " + idfFile, puts);
  }

  //modify the default unit system to I-P unit in idf_file
  this.convert_unit_to_ip = function(idfFile) {
    function puts(error, stdout, stderr) { sys.puts(stdout); }
    exec("sed -i  's/\\(HTML;.*\\)/HTML,\\n  InchPound;/g'  " + idfFile, puts);
  }

  this.run_energyplus_simulation = function(outputPath, idfName) {
    var weatherPath = this.runManager.getConfigOptions().getDefaultEPWLocation();
    var epwPath = new openstudio.path(openstudio.toString(weatherPath) + "/" + this.weather + ".epw");
    var tools = this.runManager.getConfigOptions().getTools();
    var idfPath = new openstudio.path(outputPath + "/" + idfName);
    var energyplusOutputPath = new openstudio.path(outputPath);
    var workflow = new openstudio.runmanager.Workflow("EnergyPlusPreProcess->EnergyPlus");
    workflow.add(tools);
    workflow.addParam(new openstudio.runmanager.JobParam("flatoutdir"));
    console.log("EPW path: " + openstudio.toString(epwPath) + " epw exists: " + openstudio.exists(epwPath));
   

    var job = workflow.create(energyplusOutputPath, idfPath, epwPath);
    
    this.runManager.enqueue(job, true);
    this.runManager.setPaused(false);

    this.runManager.waitForFinished();
    return job;
  }


};


exports.OpenStudioModel = OpenStudioModel;