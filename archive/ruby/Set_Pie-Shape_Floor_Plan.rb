# supports circle arcs (set radius_a=radius_b), not elliptical arcs
# supports full circle (set degree=360)
# supports perimeter zone depth (set perimeter_zone_depth > 0)

require 'openstudio'

class SetPieShapeFloorPlan < OpenStudio::Ruleset::ModelUserScript

  # override name to return the name of your script
  def name
    return "Set Pie-Shape Floor Plan"
  end

  def add_geometry_pie(model, params)

	radius_a = params['radius_a']
	radius_b = params['radius_b']
	num_points = params['num_points'] # for circle precision
	degree = params['degree']
    num_floors = params['num_floors']

	normalized_x = []
	normalized_y = []
	angle_offset = (2*Math::PI)/num_points
	arc_points = (num_points*degree/360).to_i		# for the degree of the arc 

	cx = 0
	cy = 0
	puts "Center is: #{cx}, #{cy}"
	
	core_x=[]
	core_y=[]
	
    floor_to_floor_height = params['floor_to_floor_height']
    plenum_height = params['plenum_height']
    perimeter_zone_depth = params['perimeter_zone_depth']

	for i in (0..arc_points-1)	# start from 0, run clockwise to e.g. -PI/2, -3*PI/2 ..., eventually back to -2PI
		normalized_x[i] = radius_a*Math.cos(-i*angle_offset)
		normalized_y[i] = radius_b*Math.sin(-i*angle_offset)
		core_x[i] = (radius_a-perimeter_zone_depth)*Math.cos(-i*angle_offset)
		core_y[i] = (radius_b-perimeter_zone_depth)*Math.sin(-i*angle_offset)
   		#puts "x[i] #{normalized_x[i]}, y[i] #{normalized_y[i]}, core_x[i] #{core_x[i]}, core_y[i] #{core_y[i]}"
	end

# ----------------------------------------------------------------------------------------------------------------------- #
	# Loop through the number of floors
    for floor in (0..num_floors-1)
    
      z = (floor_to_floor_height * floor)
      
      #Create a new story within the building
      story = OpenStudio::Model::BuildingStory.new(model)
      story.setNominalFloortoFloorHeight(floor_to_floor_height)
      story.setName("Story #{floor+1}")
      
      # Identity matrix for setting space origins
      m = OpenStudio::Matrix.new(4,4,0)
	  	m[0,0]=1
	  	m[1,1]=1
	  	m[2,2]=1
	  	m[3,3]=1

	  if perimeter_zone_depth > 0
		# set up core OS Points
		_OS_Points_in = []
		_OS_Points_out = []
		in_polygon = OpenStudio::Point3dVector.new
		out_polygon = OpenStudio::Point3dVector.new
		# clockwise order
	  	for i in (0..arc_points-1)
			_OS_Points_in[i] = OpenStudio::Point3d.new(core_x[i].to_f, core_y[i].to_f, z.to_f)
			_OS_Points_out[i] = OpenStudio::Point3d.new(normalized_x[i].to_f, normalized_y[i].to_f, z.to_f)												
			out_polygon << _OS_Points_out[i]
			in_polygon << _OS_Points_in[i]
			puts "Peripheral Points: (#{normalized_x[i]}, #{normalized_y[i]})"
			puts "Core Points:       (#{core_x[i]}, #{core_y[i]})"
	  	end

	  	if (degree<360)
	  		# finish out polygon here
	  		for i in (arc_points-1).downto(0)
	  			out_polygon << _OS_Points_in[i]
	    	end

	    	# finish in polygon here
			_OS_Center_Point = OpenStudio::Point3d.new(cx.to_f, cy.to_f, z.to_f)
			in_polygon << _OS_Center_Point


			core_space = OpenStudio::Model::Space::fromFloorPrint(in_polygon, floor_to_floor_height, model)
			core_space = core_space.get
			m[0,3]= _OS_Center_Point.x # last point inserted into polygon
			m[1,3]= _OS_Center_Point.y # last point inserted into polygon	
			m[2,3]= _OS_Center_Point.z # last point inserted into polygon
			core_space.changeTransformation(OpenStudio::Transformation.new(m))
	       	core_space.setBuildingStory(story)
	        core_space.setName("Story #{floor+1} Core Space")

			circular_space = OpenStudio::Model::Space::fromFloorPrint(out_polygon, floor_to_floor_height, model)
			circular_space = circular_space.get
			m[0,3]= _OS_Points_out[arc_points-1].x # last point inserted into polygon
			m[1,3]= _OS_Points_out[arc_points-1].y # last point inserted into polygon	
			m[2,3]= _OS_Points_out[arc_points-1].z # last point inserted into polygon
			circular_space.changeTransformation(OpenStudio::Transformation.new(m))
	       	circular_space.setBuildingStory(story)
	        circular_space.setName("Story #{floor+1} Outer Space")	
		else

			core_space = OpenStudio::Model::Space::fromFloorPrint(in_polygon, floor_to_floor_height, model)
			core_space = core_space.get
			m[0,3]= _OS_Points_in[arc_points-1].x # last point inserted into polygon
			m[1,3]= _OS_Points_in[arc_points-1].y # last point inserted into polygon	
			m[2,3]= _OS_Points_in[arc_points-1].z # last point inserted into polygon
			core_space.changeTransformation(OpenStudio::Transformation.new(m))
	       	core_space.setBuildingStory(story)
	        core_space.setName("Story #{floor+1} Core Space")

			whole_space = OpenStudio::Model::Space::fromFloorPrint(out_polygon, floor_to_floor_height, model)
			whole_space = whole_space.get
			m[0,3]= _OS_Points_out[arc_points-1].x # last point inserted into polygon
			m[1,3]= _OS_Points_out[arc_points-1].y # last point inserted into polygon	
			m[2,3]= _OS_Points_out[arc_points-1].z # last point inserted into polygon
			whole_space.changeTransformation(OpenStudio::Transformation.new(m))
	       	whole_space.setBuildingStory(story)
	        whole_space.setName("Story #{floor+1} Outer Space")	
	    end	

	else # minimal zone
      polygon = OpenStudio::Point3dVector.new
	  
	  _OS_Points = []

	  for i in (0..arc_points-1)
		_OS_Points[i] = OpenStudio::Point3d.new(normalized_x[i].to_f, normalized_y[i].to_f, z.to_f)
	  end
      
	  _OS_Points.each do |_OS_Point|
		puts "#{_OS_Point.x}, #{_OS_Point.y}, #{_OS_Point.z}"
	  end
	  _OS_Points.each { |point|
		polygon << point
	  }
	  polygon << OpenStudio::Point3d.new(cx.to_f, cy.to_f, z.to_f)
         
        space = OpenStudio::Model::Space::fromFloorPrint(polygon, floor_to_floor_height, model)
        space = space.get
        m[0,3] = _OS_Points[arc_points-1].x # _OS_Point[num_points-1] is the last point in the array
        m[1,3] = _OS_Points[arc_points-1].y
        m[2,3] = _OS_Points[arc_points-1].z
        space.changeTransformation(OpenStudio::Transformation.new(m))
        space.setBuildingStory(story)
        space.setName("Story #{floor+1} Core Space")
      end # end if
      #Set vertical story position
      story.setNominalZCoordinate(z)
      
    end #End of floor loop 	

    
  end
  
end

