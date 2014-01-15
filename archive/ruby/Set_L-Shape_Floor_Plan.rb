require 'openstudio'

class SetLShapeFloorPlan < OpenStudio::Ruleset::ModelUserScript

  # override name to return the name of your script
  def name
    return "Set H-Shape Floor Plan"
  end

  def add_geometry_L(model, params)

  	length = params['length']
    width = params['width']
    end1 = params['end1']
  	end2 = params['end2']
   	num_floors = params['num_floors']
    inside_length = length-end2
  	inside_width = width-end1
    
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

      bl_point = OpenStudio::Point3d.new(0,0,z)           # bottom left           tl-----tm
   	  br_point = OpenStudio::Point3d.new(length,0,z)      # bottom right          |       |
      tl_point = OpenStudio::Point3d.new(0,width,z)	      # top left              |       |
      tm_point = OpenStudio::Point3d.new(end2,width,z)    # top middle            |      mm---mr
      mm_point = OpenStudio::Point3d.new(end2,end1,z)     # middle middle         |            |
      mr_point = OpenStudio::Point3d.new(length,end1,z)   # middle right          bl-----------br
      
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
        perimeter_tm_point = tm_point + OpenStudio::Vector3d.new(-perimeter_zone_depth,-perimeter_zone_depth,0)
        perimeter_mm_point = mm_point + OpenStudio::Vector3d.new(-perimeter_zone_depth,-perimeter_zone_depth,0)
        perimeter_mr_point = mr_point + OpenStudio::Vector3d.new(-perimeter_zone_depth,-perimeter_zone_depth,0)
        
        polygon_1 = OpenStudio::Point3dVector.new
          polygon_1 << tl_point
	      polygon_1 << tm_point
		  polygon_1 << perimeter_tm_point
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
          polygon_2 << tm_point
	      polygon_2 << mm_point
		  polygon_2 << perimeter_mm_point
		  polygon_2 << perimeter_tm_point
          
        space_2 = OpenStudio::Model::Space::fromFloorPrint(polygon_2, floor_to_floor_height, model)
       	space_2 = space_2.get
        m[0,3] = perimeter_tm_point.x	# last point inserted
        m[1,3] = perimeter_tm_point.y
        m[2,3] = perimeter_tm_point.z
        space_2.changeTransformation(OpenStudio::Transformation.new(m))
        space_2.setBuildingStory(story)
        space_2.setName("Story #{floor+1} Space 2")
    
        polygon_3 = OpenStudio::Point3dVector.new
          polygon_3 << mm_point
	      polygon_3 << mr_point
		  polygon_3 << perimeter_mr_point
		  polygon_3 << perimeter_mm_point
          
        space_3 = OpenStudio::Model::Space::fromFloorPrint(polygon_3, floor_to_floor_height, model)
       	space_3 = space_3.get
        m[0,3] = perimeter_mm_point.x	# last point inserted
        m[1,3] = perimeter_mm_point.y
        m[2,3] = perimeter_mm_point.z
        space_3.changeTransformation(OpenStudio::Transformation.new(m))
        space_3.setBuildingStory(story)
        space_3.setName("Story #{floor+1} Space 3")
      
   		polygon_4 = OpenStudio::Point3dVector.new
          polygon_4 << mr_point
	      polygon_4 << br_point
		  polygon_4 << perimeter_br_point
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
          polygon_5 << br_point
	      polygon_5 << bl_point
		  polygon_5 << perimeter_bl_point
		  polygon_5 << perimeter_br_point
          
        space_5 = OpenStudio::Model::Space::fromFloorPrint(polygon_5, floor_to_floor_height, model)
       	space_5 = space_5.get
        m[0,3] = perimeter_br_point.x	# last point inserted
        m[1,3] = perimeter_br_point.y
        m[2,3] = perimeter_br_point.z
        space_5.changeTransformation(OpenStudio::Transformation.new(m))
        space_5.setBuildingStory(story)
        space_5.setName("Story #{floor+1} Space 5")
      
     	polygon_6 = OpenStudio::Point3dVector.new
          polygon_6 << bl_point
	      polygon_6 << tl_point
		  polygon_6 << perimeter_tl_point
		  polygon_6 << perimeter_bl_point
          
        space_6 = OpenStudio::Model::Space::fromFloorPrint(polygon_6, floor_to_floor_height, model)
       	space_6 = space_6.get
        m[0,3] = perimeter_bl_point.x	# last point inserted
        m[1,3] = perimeter_bl_point.y
        m[2,3] = perimeter_bl_point.z
        space_6.changeTransformation(OpenStudio::Transformation.new(m))
        space_6.setBuildingStory(story)
        space_6.setName("Story #{floor+1} Space 6")

		polygon_7 = OpenStudio::Point3dVector.new
          polygon_7 << perimeter_tl_point
	      polygon_7 << perimeter_tm_point
		  polygon_7 << perimeter_mm_point
		  polygon_7 << perimeter_bl_point
          
        space_7 = OpenStudio::Model::Space::fromFloorPrint(polygon_7, floor_to_floor_height, model)
       	space_7 = space_7.get
        m[0,3] = perimeter_bl_point.x	# last point inserted
        m[1,3] = perimeter_bl_point.y
        m[2,3] = perimeter_bl_point.z
        space_7.changeTransformation(OpenStudio::Transformation.new(m))
        space_7.setBuildingStory(story)
        space_7.setName("Story #{floor+1} Space 7")

		polygon_8 = OpenStudio::Point3dVector.new
          polygon_8 << perimeter_mm_point
	      polygon_8 << perimeter_mr_point
		  polygon_8 << perimeter_br_point
		  polygon_8 << perimeter_bl_point
          
        space_8 = OpenStudio::Model::Space::fromFloorPrint(polygon_8, floor_to_floor_height, model)
       	space_8 = space_8.get
        m[0,3] = perimeter_bl_point.x	# last point inserted
        m[1,3] = perimeter_bl_point.y
        m[2,3] = perimeter_bl_point.z
        space_8.changeTransformation(OpenStudio::Transformation.new(m))
        space_8.setBuildingStory(story)
        space_8.setName("Story #{floor+1} Space 8")
        
      # Minimal zones
      else
        l_polygon = OpenStudio::Point3dVector.new
          l_polygon << tl_point
          l_polygon << tm_point
          l_polygon << mm_point
          l_polygon << mr_point
          l_polygon << br_point
          l_polygon << bl_point
        l_space = OpenStudio::Model::Space::fromFloorPrint(l_polygon, floor_to_floor_height, model)
        l_space = l_space.get
        m[0,3] = bl_point.x
        m[1,3] = bl_point.y
        m[2,3] = bl_point.z
        l_space.changeTransformation(OpenStudio::Transformation.new(m))
        l_space.setBuildingStory(story)
        l_space.setName("Story #{floor+1} L Space")
        
      end
      
      #Set vertical story position
      story.setNominalZCoordinate(z)
      
    end #End of floor loop
    
    # runner.destroy_progress_bar
    
  end
  
end

#SetHShapeFloorPlan.new.registerWithSketchUp

