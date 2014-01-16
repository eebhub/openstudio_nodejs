require 'openstudio'

class SetHShapeFloorPlan < OpenStudio::Ruleset::ModelUserScript

  # override name to return the name of your script
  def name
    return "Set H-Shape Floor Plan"
  end

  def add_geometry_H(model, params)

  	length = params['length']
    left_width = params['left_width']
    center_width = params['center_width']
    right_width = params['right_width']
    left_end_length = params['left_end_length']
    right_end_length = params['right_end_length']
  	
  	left_upper_end_offset = params['left_upper_end_offset']
  	right_upper_end_offset = params['right_upper_end_offset']

	num_floors = params['num_floors']
    floor_to_floor_height = params['floor_to_floor_height']
    plenum_height = params['plenum_height']
    perimeter_zone_depth = params['perimeter_zone_depth']

    # Check for Cancel selection or missing inputs
    if length.nil? or left_width.nil? or center_width.nil? or right_width.nil? or left_end_length.nil? or right_end_length.nil? or left_upper_end_offset.nil? or right_upper_end_offset.nil? or num_floors.nil? or floor_to_floor_height.nil?
      #runner.okPrompt("One or more of the required inputs was left blank.")
      return false
    end

  
    if length <= 1e-4
     
      return false
    end
    
    if left_width <= 1e-4
      
      return false
    end
    
    if right_width <= 1e-4
     
      return false
    end
    
    if center_width <= 1e-4 or center_width >= ([left_width,right_width].min - 1e-4)
     
      return false
    end
    
    if left_end_length <= 1e-4 or left_end_length >= (length - 1e-4)
    
      return false
    end
    
    if right_end_length <= 1e-4 or right_end_length >= (length - left_end_length - 1e-4)
      
      return false
    end
    
    if left_upper_end_offset <= 1e-4 or left_upper_end_offset >= (left_width - center_width - 1e-4)
      
      return false
    end
    
    if right_upper_end_offset <= 1e-4 or right_upper_end_offset >= (right_width - center_width - 1e-4)
     
      return false
    end
    
    if num_floors <= 1e-4
      
      return false
    end
    
    if floor_to_floor_height <= 1e-4
      
      return false
    end
    
    if plenum_height < 0

      return false
    end
    
    shortest_side = [length/2,left_width,center_width,right_width,left_end_length,right_end_length].min
    if perimeter_zone_depth < 0 or 2*perimeter_zone_depth >= (shortest_side - 1e-4)
     
      return false
    end
   
    # Create progress bar
    # runner.create_progress_bar("Creating Spaces")
    # num_total = perimeter_zone_depth>0 ? num_floors*15 : num_floors*3
    num_complete = 0
    
    # Loop through the number of floors
    for floor in (0..num_floors-1)
    
      z = floor_to_floor_height * floor
      
      #Create a new story within the building
      story = OpenStudio::Model::BuildingStory.new(model)
      story.setNominalFloortoFloorHeight(floor_to_floor_height)
      story.setName("Story #{floor+1}")
      
      
      left_origin = (right_width - right_upper_end_offset) > (left_width - left_upper_end_offset) ? (right_width - right_upper_end_offset) - (left_width - left_upper_end_offset) : 0
      
      left_nw_point = OpenStudio::Point3d.new(0,left_width + left_origin,z)
      left_ne_point = OpenStudio::Point3d.new(left_end_length,left_width + left_origin,z)
      left_se_point = OpenStudio::Point3d.new(left_end_length,left_origin,z)
      left_sw_point = OpenStudio::Point3d.new(0,left_origin,z)
      center_nw_point = OpenStudio::Point3d.new(left_end_length,left_ne_point.y - left_upper_end_offset,z)
      center_ne_point = OpenStudio::Point3d.new(length - right_end_length,center_nw_point.y,z)
      center_se_point = OpenStudio::Point3d.new(length - right_end_length,center_nw_point.y - center_width,z)
      center_sw_point = OpenStudio::Point3d.new(left_end_length,center_se_point.y,z)
      right_nw_point = OpenStudio::Point3d.new(length - right_end_length,center_ne_point.y + right_upper_end_offset,z)
      right_ne_point = OpenStudio::Point3d.new(length,right_nw_point.y,z)
      right_se_point = OpenStudio::Point3d.new(length,right_ne_point.y-right_width,z)
      right_sw_point = OpenStudio::Point3d.new(length - right_end_length,right_se_point.y,z)
      
      # Identity matrix for setting space origins
      m = OpenStudio::Matrix.new(4,4,0)
        m[0,0] = 1
        m[1,1] = 1
        m[2,2] = 1
        m[3,3] = 1
      
      # Define polygons for a L-shape building with perimeter core zoning
      if perimeter_zone_depth > 0
        perimeter_left_nw_point = left_nw_point + OpenStudio::Vector3d.new(perimeter_zone_depth,-perimeter_zone_depth,0)
        perimeter_left_ne_point = left_ne_point + OpenStudio::Vector3d.new(-perimeter_zone_depth,-perimeter_zone_depth,0)
        perimeter_left_se_point = left_se_point + OpenStudio::Vector3d.new(-perimeter_zone_depth,perimeter_zone_depth,0)
        perimeter_left_sw_point = left_sw_point + OpenStudio::Vector3d.new(perimeter_zone_depth,perimeter_zone_depth,0)
        perimeter_center_nw_point = center_nw_point + OpenStudio::Vector3d.new(-perimeter_zone_depth,-perimeter_zone_depth,0)
        perimeter_center_ne_point = center_ne_point + OpenStudio::Vector3d.new(perimeter_zone_depth,-perimeter_zone_depth,0)
        perimeter_center_se_point = center_se_point + OpenStudio::Vector3d.new(perimeter_zone_depth,perimeter_zone_depth,0)
        perimeter_center_sw_point = center_sw_point + OpenStudio::Vector3d.new(-perimeter_zone_depth,perimeter_zone_depth,0)
        perimeter_right_nw_point = right_nw_point + OpenStudio::Vector3d.new(perimeter_zone_depth,-perimeter_zone_depth,0)
        perimeter_right_ne_point = right_ne_point + OpenStudio::Vector3d.new(-perimeter_zone_depth,-perimeter_zone_depth,0)
        perimeter_right_se_point = right_se_point + OpenStudio::Vector3d.new(-perimeter_zone_depth,perimeter_zone_depth,0)
        perimeter_right_sw_point = right_sw_point + OpenStudio::Vector3d.new(perimeter_zone_depth,perimeter_zone_depth,0)
        
        west_left_perimeter_polygon = OpenStudio::Point3dVector.new
          west_left_perimeter_polygon << left_sw_point
          west_left_perimeter_polygon << left_nw_point
          west_left_perimeter_polygon << perimeter_left_nw_point
          west_left_perimeter_polygon << perimeter_left_sw_point
        west_left_perimeter_space = OpenStudio::Model::Space::fromFloorPrint(west_left_perimeter_polygon, floor_to_floor_height, model)
        west_left_perimeter_space = west_left_perimeter_space.get
        m[0,3] = left_sw_point.x
        m[1,3] = left_sw_point.y
        m[2,3] = left_sw_point.z
        west_left_perimeter_space.changeTransformation(OpenStudio::Transformation.new(m))
        west_left_perimeter_space.setBuildingStory(story)
        west_left_perimeter_space.setName("Story #{floor+1} West Left Perimeter Space")
        
        num_complete += 1
        
        north_left_perimeter_polygon = OpenStudio::Point3dVector.new
          north_left_perimeter_polygon << left_nw_point
          north_left_perimeter_polygon << left_ne_point
          north_left_perimeter_polygon << perimeter_left_ne_point
          north_left_perimeter_polygon << perimeter_left_nw_point
        north_left_perimeter_space = OpenStudio::Model::Space::fromFloorPrint(north_left_perimeter_polygon, floor_to_floor_height, model)
        north_left_perimeter_space = north_left_perimeter_space.get
        m[0,3] = perimeter_left_nw_point.x
        m[1,3] = perimeter_left_nw_point.y
        m[2,3] = perimeter_left_nw_point.z
        north_left_perimeter_space.changeTransformation(OpenStudio::Transformation.new(m))
        north_left_perimeter_space.setBuildingStory(story)
        north_left_perimeter_space.setName("Story #{floor+1} North Left Perimeter Space")
        
        num_complete += 1
        
        east_upper_left_perimeter_polygon = OpenStudio::Point3dVector.new
          east_upper_left_perimeter_polygon << left_ne_point
          east_upper_left_perimeter_polygon << center_nw_point
          east_upper_left_perimeter_polygon << perimeter_center_nw_point
          east_upper_left_perimeter_polygon << perimeter_left_ne_point
        east_upper_left_perimeter_space = OpenStudio::Model::Space::fromFloorPrint(east_upper_left_perimeter_polygon, floor_to_floor_height, model)
        east_upper_left_perimeter_space = east_upper_left_perimeter_space.get
        m[0,3] = perimeter_center_nw_point.x
        m[1,3] = perimeter_center_nw_point.y
        m[2,3] = perimeter_center_nw_point.z
        east_upper_left_perimeter_space.changeTransformation(OpenStudio::Transformation.new(m))
        east_upper_left_perimeter_space.setBuildingStory(story)
        east_upper_left_perimeter_space.setName("Story #{floor+1} East Upper Left Perimeter Space")
        
        num_complete += 1
        
        north_center_perimeter_polygon = OpenStudio::Point3dVector.new
          north_center_perimeter_polygon << center_nw_point
          north_center_perimeter_polygon << center_ne_point
          north_center_perimeter_polygon << perimeter_center_ne_point
          north_center_perimeter_polygon << perimeter_center_nw_point
        north_center_perimeter_space = OpenStudio::Model::Space::fromFloorPrint(north_center_perimeter_polygon, floor_to_floor_height, model)
        north_center_perimeter_space = north_center_perimeter_space.get
        m[0,3] = perimeter_center_nw_point.x
        m[1,3] = perimeter_center_nw_point.y
        m[2,3] = perimeter_center_nw_point.z
        north_center_perimeter_space.changeTransformation(OpenStudio::Transformation.new(m))
        north_center_perimeter_space.setBuildingStory(story)
        north_center_perimeter_space.setName("Story #{floor+1} North Center Perimeter Space")
        
        num_complete += 1
        
        west_upper_right_perimeter_polygon = OpenStudio::Point3dVector.new
          west_upper_right_perimeter_polygon << center_ne_point
          west_upper_right_perimeter_polygon << right_nw_point
          west_upper_right_perimeter_polygon << perimeter_right_nw_point
          west_upper_right_perimeter_polygon << perimeter_center_ne_point
        west_upper_right_perimeter_space = OpenStudio::Model::Space::fromFloorPrint(west_upper_right_perimeter_polygon, floor_to_floor_height, model)
        west_upper_right_perimeter_space = west_upper_right_perimeter_space.get
        m[0,3] = center_ne_point.x
        m[1,3] = center_ne_point.y
        m[2,3] = center_ne_point.z
        west_upper_right_perimeter_space.changeTransformation(OpenStudio::Transformation.new(m))
        west_upper_right_perimeter_space.setBuildingStory(story)
        west_upper_right_perimeter_space.setName("Story #{floor+1} West Upper Right Perimeter Space")
        
        num_complete += 1
        
        north_right_perimeter_polygon = OpenStudio::Point3dVector.new
          north_right_perimeter_polygon << right_nw_point
          north_right_perimeter_polygon << right_ne_point
          north_right_perimeter_polygon << perimeter_right_ne_point
          north_right_perimeter_polygon << perimeter_right_nw_point
        north_right_perimeter_space = OpenStudio::Model::Space::fromFloorPrint(north_right_perimeter_polygon, floor_to_floor_height, model)
        north_right_perimeter_space = north_right_perimeter_space.get
        m[0,3] = perimeter_right_nw_point.x
        m[1,3] = perimeter_right_nw_point.y
        m[2,3] = perimeter_right_nw_point.z
        north_right_perimeter_space.changeTransformation(OpenStudio::Transformation.new(m))
        north_right_perimeter_space.setBuildingStory(story)
        north_right_perimeter_space.setName("Story #{floor+1} North Right Perimeter Space")
        
        num_complete += 1
        
        east_right_perimeter_polygon = OpenStudio::Point3dVector.new
          east_right_perimeter_polygon << right_ne_point
          east_right_perimeter_polygon << right_se_point
          east_right_perimeter_polygon << perimeter_right_se_point
          east_right_perimeter_polygon << perimeter_right_ne_point
        east_right_perimeter_space = OpenStudio::Model::Space::fromFloorPrint(east_right_perimeter_polygon, floor_to_floor_height, model)
        east_right_perimeter_space = east_right_perimeter_space.get
        m[0,3] = perimeter_right_se_point.x
        m[1,3] = perimeter_right_se_point.y
        m[2,3] = perimeter_right_se_point.z
        east_right_perimeter_space.changeTransformation(OpenStudio::Transformation.new(m))
        east_right_perimeter_space.setBuildingStory(story)
        east_right_perimeter_space.setName("Story #{floor+1} East Right Perimeter Space")
        
        num_complete += 1
        
        south_right_perimeter_polygon = OpenStudio::Point3dVector.new
          south_right_perimeter_polygon << right_se_point
          south_right_perimeter_polygon << right_sw_point
          south_right_perimeter_polygon << perimeter_right_sw_point
          south_right_perimeter_polygon << perimeter_right_se_point
        south_right_perimeter_space = OpenStudio::Model::Space::fromFloorPrint(south_right_perimeter_polygon, floor_to_floor_height, model)
        south_right_perimeter_space = south_right_perimeter_space.get
        m[0,3] = right_sw_point.x
        m[1,3] = right_sw_point.y
        m[2,3] = right_sw_point.z
        south_right_perimeter_space.changeTransformation(OpenStudio::Transformation.new(m))
        south_right_perimeter_space.setBuildingStory(story)
        south_right_perimeter_space.setName("Story #{floor+1} South Right Perimeter Space")
        
        num_complete += 1
        
        west_lower_right_perimeter_polygon = OpenStudio::Point3dVector.new
          west_lower_right_perimeter_polygon << right_sw_point
          west_lower_right_perimeter_polygon << center_se_point
          west_lower_right_perimeter_polygon << perimeter_center_se_point
          west_lower_right_perimeter_polygon << perimeter_right_sw_point
        west_lower_right_perimeter_space = OpenStudio::Model::Space::fromFloorPrint(west_lower_right_perimeter_polygon, floor_to_floor_height, model)
        west_lower_right_perimeter_space = west_lower_right_perimeter_space.get
        m[0,3] = right_sw_point.x
        m[1,3] = right_sw_point.y
        m[2,3] = right_sw_point.z
        west_lower_right_perimeter_space.changeTransformation(OpenStudio::Transformation.new(m))
        west_lower_right_perimeter_space.setBuildingStory(story)
        west_lower_right_perimeter_space.setName("Story #{floor+1} West Lower Right Perimeter Space")
        
        num_complete += 1
        
        south_center_perimeter_polygon = OpenStudio::Point3dVector.new
          south_center_perimeter_polygon << center_se_point
          south_center_perimeter_polygon << center_sw_point
          south_center_perimeter_polygon << perimeter_center_sw_point
          south_center_perimeter_polygon << perimeter_center_se_point
        south_center_perimeter_space = OpenStudio::Model::Space::fromFloorPrint(south_center_perimeter_polygon, floor_to_floor_height, model)
        south_center_perimeter_space = south_center_perimeter_space.get
        m[0,3] = center_sw_point.x
        m[1,3] = center_sw_point.y
        m[2,3] = center_sw_point.z
        south_center_perimeter_space.changeTransformation(OpenStudio::Transformation.new(m))
        south_center_perimeter_space.setBuildingStory(story)
        south_center_perimeter_space.setName("Story #{floor+1} South Center Perimeter Space")
        
        num_complete += 1
        
        east_lower_left_perimeter_polygon = OpenStudio::Point3dVector.new
          east_lower_left_perimeter_polygon << center_sw_point
          east_lower_left_perimeter_polygon << left_se_point
          east_lower_left_perimeter_polygon << perimeter_left_se_point
          east_lower_left_perimeter_polygon << perimeter_center_sw_point
        east_lower_left_perimeter_space = OpenStudio::Model::Space::fromFloorPrint(east_lower_left_perimeter_polygon, floor_to_floor_height, model)
        east_lower_left_perimeter_space = east_lower_left_perimeter_space.get
        m[0,3] = perimeter_left_se_point.x
        m[1,3] = perimeter_left_se_point.y
        m[2,3] = perimeter_left_se_point.z
        east_lower_left_perimeter_space.changeTransformation(OpenStudio::Transformation.new(m))
        east_lower_left_perimeter_space.setBuildingStory(story)
        east_lower_left_perimeter_space.setName("Story #{floor+1} East Lower Left Perimeter Space")
        
        num_complete += 1
        
        south_left_perimeter_polygon = OpenStudio::Point3dVector.new
          south_left_perimeter_polygon << left_se_point
          south_left_perimeter_polygon << left_sw_point
          south_left_perimeter_polygon << perimeter_left_sw_point
          south_left_perimeter_polygon << perimeter_left_se_point
        south_left_perimeter_space = OpenStudio::Model::Space::fromFloorPrint(south_left_perimeter_polygon, floor_to_floor_height, model)
        south_left_perimeter_space = south_left_perimeter_space.get
        m[0,3] = left_sw_point.x
        m[1,3] = left_sw_point.y
        m[2,3] = left_sw_point.z
        south_left_perimeter_space.changeTransformation(OpenStudio::Transformation.new(m))
        south_left_perimeter_space.setBuildingStory(story)
        south_left_perimeter_space.setName("Story #{floor+1} South Left Perimeter Space")
        
        num_complete += 1
        
        west_core_polygon = OpenStudio::Point3dVector.new
          west_core_polygon << perimeter_left_sw_point
          west_core_polygon << perimeter_left_nw_point
          west_core_polygon << perimeter_left_ne_point
          west_core_polygon << perimeter_center_nw_point
          west_core_polygon << perimeter_center_sw_point
          west_core_polygon << perimeter_left_se_point
        west_core_space = OpenStudio::Model::Space::fromFloorPrint(west_core_polygon, floor_to_floor_height, model)
        west_core_space = west_core_space.get
        m[0,3] = perimeter_left_sw_point.x
        m[1,3] = perimeter_left_sw_point.y
        m[2,3] = perimeter_left_sw_point.z
        west_core_space.changeTransformation(OpenStudio::Transformation.new(m))
        west_core_space.setBuildingStory(story)
        west_core_space.setName("Story #{floor+1} West Core Space")
        
        num_complete += 1
        
        center_core_polygon = OpenStudio::Point3dVector.new
          center_core_polygon << perimeter_center_sw_point
          center_core_polygon << perimeter_center_nw_point
          center_core_polygon << perimeter_center_ne_point
          center_core_polygon << perimeter_center_se_point
        center_core_space = OpenStudio::Model::Space::fromFloorPrint(center_core_polygon, floor_to_floor_height, model)
        center_core_space = center_core_space.get
        m[0,3] = perimeter_center_sw_point.x
        m[1,3] = perimeter_center_sw_point.y
        m[2,3] = perimeter_center_sw_point.z
        center_core_space.changeTransformation(OpenStudio::Transformation.new(m))
        center_core_space.setBuildingStory(story)
        center_core_space.setName("Story #{floor+1} Center Core Space")
        
        num_complete += 1
        
        east_core_polygon = OpenStudio::Point3dVector.new
          east_core_polygon << perimeter_right_sw_point
          east_core_polygon << perimeter_center_se_point
          east_core_polygon << perimeter_center_ne_point
          east_core_polygon << perimeter_right_nw_point
          east_core_polygon << perimeter_right_ne_point
          east_core_polygon << perimeter_right_se_point
        east_core_space = OpenStudio::Model::Space::fromFloorPrint(east_core_polygon, floor_to_floor_height, model)
        east_core_space = east_core_space.get
        m[0,3] = perimeter_right_sw_point.x
        m[1,3] = perimeter_right_sw_point.y
        m[2,3] = perimeter_right_sw_point.z
        east_core_space.changeTransformation(OpenStudio::Transformation.new(m))
        east_core_space.setBuildingStory(story)
        east_core_space.setName("Story #{floor+1} East Core Space")
        
        num_complete += 1
        
      # Minimal zones
      else
        west_polygon = OpenStudio::Point3dVector.new
          west_polygon << left_sw_point
          west_polygon << left_nw_point
          west_polygon << left_ne_point
          west_polygon << center_nw_point
          west_polygon << center_sw_point
          west_polygon << left_se_point
        west_space = OpenStudio::Model::Space::fromFloorPrint(west_polygon, floor_to_floor_height, model)
        west_space = west_space.get
        m[0,3] = left_sw_point.x
        m[1,3] = left_sw_point.y
        m[2,3] = left_sw_point.z
        west_space.changeTransformation(OpenStudio::Transformation.new(m))
        west_space.setBuildingStory(story)
        west_space.setName("Story #{floor+1} West Space")
        
        num_complete += 1
        
        center_polygon = OpenStudio::Point3dVector.new
          center_polygon << center_sw_point
          center_polygon << center_nw_point
          center_polygon << center_ne_point
          center_polygon << center_se_point
        center_space = OpenStudio::Model::Space::fromFloorPrint(center_polygon, floor_to_floor_height, model)
        center_space = center_space.get
        m[0,3] = center_sw_point.x
        m[1,3] = center_sw_point.y
        m[2,3] = center_sw_point.z
        center_space.changeTransformation(OpenStudio::Transformation.new(m))
        center_space.setBuildingStory(story)
        center_space.setName("Story #{floor+1} Center Space")
        
        num_complete += 1
        
        east_polygon = OpenStudio::Point3dVector.new
          east_polygon << right_sw_point
          east_polygon << center_se_point
          east_polygon << center_ne_point
          east_polygon << right_nw_point
          east_polygon << right_ne_point
          east_polygon << right_se_point
        east_space = OpenStudio::Model::Space::fromFloorPrint(east_polygon, floor_to_floor_height, model)
        east_space = east_space.get
        m[0,3] = right_sw_point.x
        m[1,3] = right_sw_point.y
        m[2,3] = right_sw_point.z
        east_space.changeTransformation(OpenStudio::Transformation.new(m))
        east_space.setBuildingStory(story)
        east_space.setName("Story #{floor+1} East Space")
        
        num_complete += 1
        
      end
      
      #Set vertical story position
      story.setNominalZCoordinate(z)
      
    end #End of floor loop
   
  end #End method
  
end #End class


