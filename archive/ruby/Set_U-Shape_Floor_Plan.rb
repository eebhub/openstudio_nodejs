require 'openstudio'

class SetUShapeFloorPlan < OpenStudio::Ruleset::ModelUserScript

  # override name to return the name of your script
  def name
    return "Set U-Shape Floor Plan"
  end

  def add_geometry_U(model, params)

    length = params['length']
    width1 = params['width1']
    width2 = params['width2']
    end1 = params['end1']
  	end2 = params['end2']
  	offset = params['offset']
    num_floors = params['num_floors']

    floor_to_floor_height = 3.8
    plenum_height = 1.0
    perimeter_zone_depth = (4.57).to_i

    # Loop through the number of floors
    for floor in (0..num_floors-1)
    
      z = floor_to_floor_height * floor
      
      #Create a new story within the building
      story = OpenStudio::Model::BuildingStory.new(model)
      story.setNominalFloortoFloorHeight(floor_to_floor_height)
      story.setName("Story #{floor+1}")
      
	  # assume bottom left point of L is the origin      

      bl_point = OpenStudio::Point3d.new(0,0,z)										# bottom left		
   	  br_point = OpenStudio::Point3d.new(length,0,z)								# bottom right         
	  tl_point = OpenStudio::Point3d.new(0,width1,z)								# top left           
	  tr_point = OpenStudio::Point3d.new(length,width2,z)							# top right          
	  ml_point = OpenStudio::Point3d.new(end1,width1-offset,z)						# middle middle         
	  mr_point = OpenStudio::Point3d.new(length-end2,width1-offset,z)				# middle right         
      tml_point = OpenStudio::Point3d.new(end1,width1,z)
	  tmr_point = OpenStudio::Point3d.new(length-end2,width2,z)

      # Identity matrix for setting space origins
      m = OpenStudio::Matrix.new(4,4,0)
        m[0,0] = 1
        m[1,1] = 1
        m[2,2] = 1
        m[3,3] = 1
      
      # Define polygons for a L-shape building with perimeter core zoning
      if perimeter_zone_depth > 0
        perimeter_bl_point = bl_point + OpenStudio::Vector3d.new(perimeter_zone_depth,perimeter_zone_depth,0)
        perimeter_br_point = br_point + OpenStudio::Vector3d.new(-perimeter_zone_depth,perimeter_zone_depth,0)
        perimeter_tl_point = tl_point + OpenStudio::Vector3d.new(perimeter_zone_depth,-perimeter_zone_depth,0)
        perimeter_tr_point = tr_point + OpenStudio::Vector3d.new(-perimeter_zone_depth,-perimeter_zone_depth,0)
        perimeter_ml_point = ml_point + OpenStudio::Vector3d.new(-perimeter_zone_depth,-perimeter_zone_depth,0)
        perimeter_mr_point = mr_point + OpenStudio::Vector3d.new(perimeter_zone_depth,-perimeter_zone_depth,0)
		perimeter_tml_point = tml_point + OpenStudio::Vector3d.new(-perimeter_zone_depth,-perimeter_zone_depth,0)
        perimeter_tmr_point = tmr_point + OpenStudio::Vector3d.new(perimeter_zone_depth,-perimeter_zone_depth,0)
        
        polygon_1 = OpenStudio::Point3dVector.new
          polygon_1 << tl_point
	      polygon_1 << tml_point
		  polygon_1 << perimeter_tml_point
		  polygon_1 << perimeter_tl_point
          
        space_1 = OpenStudio::Model::Space::fromFloorPrint(polygon_1, floor_to_floor_height, model)
       	space_1 = space_1.get
        m[0,3] = perimeter_tl_point.x	# last point inserted
        m[1,3] = perimeter_tl_point.y
        m[2,3] = perimeter_tl_point.z
        space_1.changeTransformation(OpenStudio::Transformation.new(m))
        space_1.setBuildingStory(story)
        space_1.setName("Story #{floor+1} Space 1")
        
    
        polygon_2 = OpenStudio::Point3dVector.new
          polygon_2 << tml_point
	      polygon_2 << ml_point
		  polygon_2 << perimeter_ml_point
		  polygon_2 << perimeter_tml_point
          
        space_2 = OpenStudio::Model::Space::fromFloorPrint(polygon_2, floor_to_floor_height, model)
       	space_2 = space_2.get
        m[0,3] = perimeter_tml_point.x	# last point inserted
        m[1,3] = perimeter_tml_point.y
        m[2,3] = perimeter_tml_point.z
        space_2.changeTransformation(OpenStudio::Transformation.new(m))
        space_2.setBuildingStory(story)
        space_2.setName("Story #{floor+1} Space 2")
    
        polygon_3 = OpenStudio::Point3dVector.new
          polygon_3 << ml_point
	      polygon_3 << mr_point
		  polygon_3 << perimeter_mr_point
		  polygon_3 << perimeter_ml_point
          
        space_3 = OpenStudio::Model::Space::fromFloorPrint(polygon_3, floor_to_floor_height, model)
       	space_3 = space_3.get
        m[0,3] = perimeter_ml_point.x	# last point inserted
        m[1,3] = perimeter_ml_point.y
        m[2,3] = perimeter_ml_point.z
        space_3.changeTransformation(OpenStudio::Transformation.new(m))
        space_3.setBuildingStory(story)
        space_3.setName("Story #{floor+1} Space 3")
      
   		polygon_4 = OpenStudio::Point3dVector.new
          polygon_4 << mr_point
	      polygon_4 << tmr_point
		  polygon_4 << perimeter_tmr_point
		  polygon_4 << perimeter_mr_point
          
        space_4 = OpenStudio::Model::Space::fromFloorPrint(polygon_4, floor_to_floor_height, model)
       	space_4 = space_4.get
        m[0,3] = perimeter_mr_point.x	# last point inserted
        m[1,3] = perimeter_mr_point.y
        m[2,3] = perimeter_mr_point.z
        space_4.changeTransformation(OpenStudio::Transformation.new(m))
        space_4.setBuildingStory(story)
        space_4.setName("Story #{floor+1} Space 4")

		polygon_5 = OpenStudio::Point3dVector.new
          polygon_5 << tmr_point
	      polygon_5 << tr_point
		  polygon_5 << perimeter_tr_point
		  polygon_5 << perimeter_tmr_point
          
        space_5 = OpenStudio::Model::Space::fromFloorPrint(polygon_5, floor_to_floor_height, model)
       	space_5 = space_5.get
        m[0,3] = perimeter_tmr_point.x	# last point inserted
        m[1,3] = perimeter_tmr_point.y
        m[2,3] = perimeter_tmr_point.z
        space_5.changeTransformation(OpenStudio::Transformation.new(m))
        space_5.setBuildingStory(story)
        space_5.setName("Story #{floor+1} Space 5")
      
     	polygon_6 = OpenStudio::Point3dVector.new
          polygon_6 << tr_point
	      polygon_6 << br_point
		  polygon_6 << perimeter_br_point
		  polygon_6 << perimeter_tr_point
          
        space_6 = OpenStudio::Model::Space::fromFloorPrint(polygon_6, floor_to_floor_height, model)
       	space_6 = space_6.get
        m[0,3] = perimeter_tr_point.x	# last point inserted
        m[1,3] = perimeter_tr_point.y
        m[2,3] = perimeter_tr_point.z
        space_6.changeTransformation(OpenStudio::Transformation.new(m))
        space_6.setBuildingStory(story)
        space_6.setName("Story #{floor+1} Space 6")

		polygon_7 = OpenStudio::Point3dVector.new
          polygon_7 << br_point
	      polygon_7 << bl_point
		  polygon_7 << perimeter_bl_point
		  polygon_7 << perimeter_br_point
          
        space_7 = OpenStudio::Model::Space::fromFloorPrint(polygon_7, floor_to_floor_height, model)
       	space_7 = space_7.get
        m[0,3] = perimeter_br_point.x	# last point inserted
        m[1,3] = perimeter_br_point.y
        m[2,3] = perimeter_br_point.z
        space_7.changeTransformation(OpenStudio::Transformation.new(m))
        space_7.setBuildingStory(story)
        space_7.setName("Story #{floor+1} Space 7")

		polygon_8 = OpenStudio::Point3dVector.new
          polygon_8 << bl_point
	      polygon_8 << tl_point
		  polygon_8 << perimeter_tl_point
		  polygon_8 << perimeter_bl_point
          
        space_8 = OpenStudio::Model::Space::fromFloorPrint(polygon_8, floor_to_floor_height, model)
       	space_8 = space_8.get
        m[0,3] = perimeter_bl_point.x	# last point inserted
        m[1,3] = perimeter_bl_point.y
        m[2,3] = perimeter_bl_point.z
        space_8.changeTransformation(OpenStudio::Transformation.new(m))
        space_8.setBuildingStory(story)
        space_8.setName("Story #{floor+1} Space 8")

		polygon_9 = OpenStudio::Point3dVector.new
          polygon_9 << perimeter_tl_point
	      polygon_9 << perimeter_tml_point
		  polygon_9 << perimeter_ml_point
		  polygon_9 << perimeter_bl_point
          
        space_9 = OpenStudio::Model::Space::fromFloorPrint(polygon_9, floor_to_floor_height, model)
       	space_9 = space_9.get
        m[0,3] = perimeter_bl_point.x	# last point inserted
        m[1,3] = perimeter_bl_point.y
        m[2,3] = perimeter_bl_point.z
        space_9.changeTransformation(OpenStudio::Transformation.new(m))
        space_9.setBuildingStory(story)
        space_9.setName("Story #{floor+1} Space 9")

		polygon_10 = OpenStudio::Point3dVector.new
          polygon_10 << perimeter_ml_point
	      polygon_10 << perimeter_mr_point
		  polygon_10 << perimeter_br_point
		  polygon_10 << perimeter_bl_point
          
        space_10 = OpenStudio::Model::Space::fromFloorPrint(polygon_10, floor_to_floor_height, model)
       	space_10 = space_10.get
        m[0,3] = perimeter_bl_point.x	# last point inserted
        m[1,3] = perimeter_bl_point.y
        m[2,3] = perimeter_bl_point.z
        space_10.changeTransformation(OpenStudio::Transformation.new(m))
        space_10.setBuildingStory(story)
        space_10.setName("Story #{floor+1} Space 10")

		polygon_11 = OpenStudio::Point3dVector.new
          polygon_11 << perimeter_mr_point
	      polygon_11 << perimeter_tmr_point
		  polygon_11 << perimeter_tr_point
		  polygon_11 << perimeter_br_point
          
        space_11 = OpenStudio::Model::Space::fromFloorPrint(polygon_11, floor_to_floor_height, model)
       	space_11 = space_11.get
        m[0,3] = perimeter_br_point.x	# last point inserted
        m[1,3] = perimeter_br_point.y
        m[2,3] = perimeter_br_point.z
        space_11.changeTransformation(OpenStudio::Transformation.new(m))
        space_11.setBuildingStory(story)
        space_11.setName("Story #{floor+1} Space 11")        

      # Minimal zones
      else
        u_polygon = OpenStudio::Point3dVector.new
          u_polygon << tl_point
          u_polygon << tml_point
          u_polygon << ml_point
          u_polygon << mr_point
		  u_polygon << tmr_point
          u_polygon << tr_point
          u_polygon << br_point
          u_polygon << bl_point
        u_space = OpenStudio::Model::Space::fromFloorPrint(u_polygon, floor_to_floor_height, model)
        u_space = u_space.get
        m[0,3] = bl_point.x
        m[1,3] = bl_point.y
        m[2,3] = bl_point.z
        u_space.changeTransformation(OpenStudio::Transformation.new(m))
        u_space.setBuildingStory(story)
        u_space.setName("Story #{floor+1} U Space")
        
      end
      
      #Set vertical story position
      story.setNominalZCoordinate(z)
      
    end #End of floor loop
    
    # runner.destroy_progress_bar
    
  end
  
end


