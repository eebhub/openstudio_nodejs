<?php

//START SESSION
session_start();
$timestamp = time();

// basic building and user's info
$user = $_POST[random_number];
$email = "EMPTY";
$buildingName = $_SESSION[building_name] = str_replace(' ', '', $_POST[building_name]);
$city = $_SESSION[weather_epw_location] = $_POST[weather_epw_location];
$tightness = $_POST['tightness'];


$primary_spacetype = $_SESSION[activity_type] = $_POST[activity_type];
// secondary space type is based on primary space type
switch($primary_spacetype) {
    case "SmallOffice":
    case "MediumOffice":
    case "LargeOffice":
        $_POST[activity_type_specific] = 'WholeBuilding'; 
        break;
    case "Warehouse":
        $_POST[activity_type_specific] = 'Office'; 
        break;
    case "Retail":
        $_POST[activity_type_specific] = 'Core'; 
        break;
}
$secondary_spacetype = $_SESSION[activity_type_specific] = $_POST[activity_type_specific];

// material and area    
$floors = $_SESSION[number_of_floors] = $_POST[number_of_floors];
$floorArea = 1; //$_SESSION[gross_floor_area] = $_POST[gross_floor_area];   // not use yet, area is re-define from the geometric below
$roofMaterial = $_SESSION[roof_type] = $_POST[roof_type];                 
$wallMaterial = $_SESSION[exterior_wall_type] = $_POST[exterior_wall_type];
$windowPercent = $_SESSION[window_to_wall_ratio] = $_POST[window_to_wall_ratio]/100;
$shape = $_SESSION[footprint_shape] = $_POST[footprint_shape];

// lighting
$room_depth = $_SESSION[room_depth] = $_POST[room_depth];
$room_width = $_SESSION[room_width] = $_POST[room_width];
$room_height = $_SESSION[room_height] = $_POST[room_height];
$wall_thickness = $_SESSION[wall_thickness] = $_POST[wall_thickness];
$floor_to_floor_height = $_SESSION[floor_to_floor_height] = $_POST[floor_to_floor_height];
$building_height = ($floor_to_floor_height)*$number_of_floors;
$window_to_wall_ratio = $_SESSION[window_to_wall_ratio] = $_POST[window_to_wall_ratio];
$window_head_height = $_SESSION[window_head_height] = $_POST[window_head_height];
$building_orientation = $_SESSION[building_orientation] = $_POST[building_orientation];
$overhang_depth = $_SESSION[overhang_depth] = $_POST[overhang_depth];
$lighting_power_density = $_SESSION[lighting_power_density] = $_POST[lighting_power_density];
$illuminance = $_SESSION[illuminance] = $_POST[illuminance];
$ceiling_reflectance = $_SESSION[ceiling_reflectance] = $_POST[ceiling_reflectance];
$wall_reflectance = $_SESSION[wall_reflectance] = $_POST[wall_reflectance];
$floor_reflectance = $_SESSION[floor_reflectance] = $_POST[floor_reflectance];
$window_transmittance = $_SESSION[window_transmittance] = $_POST[window_transmittance];
$interior_shading_type = $_SESSION[interior_shading_type] = $_POST[interior_shading_type];

// user directory to save all files
$user_dir = $buildingName.md5($_SERVER['REMOTE_ADDR'].time()); 
$_SESSION[user_dir] = $user_dir;
mkdir("/home/bitnami/public_html/openstudio/outputs/eem/".$user_dir, 0775);


//RUN OPENSTUDIO
$rubyCmdCreateIDF = "xvfb-run -a ruby run_eebhub.rb ".
            				$user.' '.					
							$email.' '.					
							'"'.$buildingName.'" '.	        			
							$city.' '.					
							$tightness.' '. 
                            $primary_spacetype.' '.
                            $secondary_spacetype.' '.
                            $floors.' '.
                            $floorArea.' "'.
                            $roofMaterial.'" "'. 
                            $wallMaterial.'" '.
                            $windowPercent.' '.
                            $shape;					
          
// geometric info                 
switch($shape) {
    case "Rectangle": 
        $rubyCmdCreateIDF = $rubyCmdCreateIDF.' '.
            				$_POST['length'].' '.					
							$_POST['width'];
        break;
        
    case "H": 
        $rubyCmdCreateIDF = $rubyCmdCreateIDF.' '.
            				$_POST['length'].' '.					
							$_POST['left_width'].' '.
							$_POST['center_width'].' '.					
							$_POST['right_width'].' '.
							$_POST['left_end_length'].' '.					
							$_POST['right_end_length'].' '.
							$_POST['left_upper_end_offset'].' '.					
							$_POST['right_upper_end_offset'];
        break;
        
    case "L":  
        $rubyCmdCreateIDF = $rubyCmdCreateIDF.' '.
            				$_POST['length'].' '.					
							$_POST['width'].' '.
							$_POST['end_1'].' '.					
							$_POST['end_2'];
        break;
        
    case "T": 
        $rubyCmdCreateIDF = $rubyCmdCreateIDF.' '.
            				$_POST['length'].' '.					
							$_POST['width'].' '.
							$_POST['end_1'].' '.					
							$_POST['end_2'].' '.
							$_POST['offset'];
        break;
        
    case "U": 
        $rubyCmdCreateIDF = $rubyCmdCreateIDF.' '.
            				$_POST['length'].' '.					
							$_POST['width_1'].' '.					
							$_POST['width_2'].' '.
							$_POST['end_1'].' '.
							$_POST['end_2'].' '.
							$_POST['offset'];
        break;
        
    case "Pie":
        $rubyCmdCreateIDF = $rubyCmdCreateIDF.' '.
            				$_POST['radius_a'].' '.					
							$_POST['radius_b'].' '.
							$_POST['num_points'].' '.					
							$_POST['degree'];
        break;
    
}
                           
echo "check floors: ", $floors, "<br>";
echo "check wwr: ", $windowPercent, "<br>";                           
echo "check command line: ", $rubyCmdCreateIDF, "<br>";                    
//echo $tightness;

echo $run = shell_exec($rubyCmdCreateIDF);
echo "check output result: ", $run, "<br>";

// baseline modelname
echo $_SESSION['Model'][0] = $_SESSION['cur_model'] = "Simulation_$user.idf";
//$_SESSION['eem1_model'] = "EEM1_Simulation_$user.idf";
//$_SESSION['eem2_model'] = "EEM2_EEM1_Simulation_$user.idf";
//$_SESSION['eem3_model'] = "EEM3_EEM2_EEM1_Simulation_$user.idf";


// create EEM in run 2
//$run2 = shell_exec("xvfb-run -n $server_number ruby run_eem.rb ".$_SESSION['cur_model']);
//echo $run2;

// if you would like the print the outputs on screen please comment out the following line.
header("location: http://rmt.eebhub.org/comprehensive2/tracking-sheet.php");
?>
