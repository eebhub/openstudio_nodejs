<?php 
require 'php/EEB_SQLITE3.php';

echo "<br><a href='tracking-sheet.php' > Go Back to tracking-sheet.php </a><br>";

// start a session
session_start();

$_SESSION['installed_measures'][$_SESSION['current_yr']] = $_POST['selected'];    // update the installed measure list by year
$user_dir = $_SESSION[user_dir];                                                // the name of dir which result files is stored 

// update the eem version number
if($_SESSION['eem_cnt'] < 1) {       
   
    // initialize the eem version to 1
	//echo $user_dir;
        copy("idf/$_SESSION[cur_model]", "eem/$user_dir/temp.idf");
	
	// run autosizing to the baseline 
	$cmd = "xvfb-run -a ruby ./eeb_rb/autosize.rb $user_dir/temp.idf $_SESSION[cur_model]";
	$run = shell_exec($cmd);
	
    $version = $_SESSION['eem_cnt'] = 1;   // eem version 
	
} else {
 
    // update the eem version number 
    $version = ++$_SESSION['eem_cnt'];
	
	$pre_version = $version-1;
	// use the previous the version as pre-measured eem file
    copy("eem/$user_dir/eem_{$pre_version}.idf", "eem/$user_dir/temp.idf");
}


$file_in = "eem/$user_dir/temp.idf";                       // the name of the pre-measured eem file  
$file_out = "eem/$user_dir/improved_eem.idf";              // the name of the post-measured eem file
$file_save = "eem/$user_dir/eem_{$version}.idf";           // the name of the file is ready to save

/*
 *  function: reset a single measure field in idf file
 *  $file_source: input file name, type=>string
 *  $file_target: output file name, type=>string
 *  $header: field header, type=>string
 *  $replace_str: body of field, type=>string 
 */
function setMeasure($file_source, $file_target, $header, $replace_str) {

    $rh = fopen($file_source, 'rb');
    $wh = fopen($file_target, 'wb');

	// section indicators 
	$def_section = false;

    if ($rh===false || $wh===false) {
		// error reading or opening file
		return true;
    }

    while (!feof($rh) && ($buffer = fgets($rh, 4096)) !== false) {

        // start of the section 
        $pattern = $header;
		if(preg_match($pattern, $buffer, $matches))
		{
			$def_section = true;
		}	

		// Replace the default section
		if($def_section == true&&!preg_match('/.*;.*/', $buffer, $matches)) {
			$buffer = '';
		}

		// end of the section of electric equipment  
		$pattern = '/.*;.*/';
		if($def_section == true&&preg_match($pattern, $buffer, $matches))
		{

			$buffer = $replace_str;
			$def_section = false;
		}

		// Write to file_target 
		if (fwrite($wh, $buffer) === FALSE) {
		   // 'Download error: Cannot write to file ('.$file_target.')';
		   return true;
		}
    }
    fclose($rh);
    fclose($wh);

    copy($file_target, $file_source);

    // No error
    return false;
}

/*
 *  function: set the design flow rate by percentage
 *  $file_source: the name of the input file, type=>string
 *  $file_target: the name of the output file, type=>string
 *  $percentage: the percentage of flow rate decreases(eg: $percentage=0.8 <=> decrease 20%), type=>number   
 */
function setInfiltration($file_source, $file_target, $percentage) {

	$header = "/.*ZoneInfiltration:DesignFlowRate,.*/";
    $rh = fopen($file_source, 'rb');
    $wh = fopen($file_target, 'wb');

	// section indicators 
	$def_section = false;

    if ($rh===false || $wh===false) {
		// error reading or opening file
		return true;
    }

	// read each line from file_source
    while (!feof($rh) && ($buffer = fgets($rh, 4096)) !== false) {

        // start of the section 
        $pattern = $header;
		if(preg_match($pattern, $buffer, $matches))
		{
			$def_section = true;
		}	

		// set the designed flow rate by the persentage
		if($def_section == true&&preg_match('/.*!- Design Flow Rate \{m3\/s\}.*/', $buffer, $matches)) {
			$flowRate = preg_replace('/\(0.[0-9]+\),.*/', '$1',$buffer)*$percentage;
            $buffer = "  ".$flowRate.",                       !- Design Flow Rate {m3/s}\n";
		}

		// end of the section 
		$pattern = '/.*;.*/';
		if($def_section == true&&preg_match($pattern, $buffer, $matches))
		{
			$def_section = false;
		}

		// append the line to file_target 
		if (fwrite($wh, $buffer) === FALSE) {
		   echo 'Download error: Cannot write to file ('.$file_target.')';
		   return true;            // return error
		}
    }
    fclose($rh);
    fclose($wh);

    copy($file_target, $file_source);

    // No error
    return false;
}

/*
 *  function: set the design flow rate by percentage
 *  $file_source: the name of the input file, type=>string
 *  $file_target: the name of the output file, type=>string
 */
function setVentilation($file_source, $file_target) {

	$header = "/.*Fan Variable Volume 1,.*!- Name.*/";
    $rh = fopen($file_source, 'rb');
    $wh = fopen($file_target, 'wb');

	// section indicators 
	$def_section = false;

    if ($rh===false || $wh===false) {
		// error reading or opening file
		return true;
    }

    while (!feof($rh) && ($buffer = fgets($rh, 4096)) !== false) {

        // start of the section 
        $pattern = $header;
		if(preg_match($pattern, $buffer, $matches))
		{
			$def_section = true;
			// keep the same line
		}	
		// set the schedule name
		else if($def_section == true&&preg_match('/.*!- Availability Schedule Name.*/', $buffer, $matches)) {
            $buffer = "  VentilationSetback,                        !- Design Flow Rate {m3/s}\n".
					  "  0.5,                                       !- Fan Efficiency\n".
					  "  1200,                                      !- Pressure Rise {Pa}\n".
					  "  24.58337,                                  !- Maximum Flow Rate {m3/s}\n";
		}
		// set the fan power input method
		else if($def_section == true&&preg_match('/.*!- Fan Power Minimum Flow Rate Input Method.*/', $buffer, $matches)) {
			$buffer = "  Fraction,                      !- Design Flow Rate {m3/s}\n". 
					  "	 0,                             !- Fan Power Minimum Flow Fraction\n".
					  "  0,                       		!- Fan Power Minimum Air Flow Rate {m3/s}\n".
					  "  0.93,                    		!- Motor Efficiency\n".
					  "  1,                       		!- Motor In Airstream Fraction\n".
					  "  0.040759894,             		!- Fan Power Coefficient 1\n".
					  "  0.08804497,              		!- Fan Power Coefficient 2\n".
					  "  -0.07292612,             		!- Fan Power Coefficient 3\n".
					  "  0.943739823,             		!- Fan Power Coefficient 4\n".
					  "  0,                       		!- Fan Power Coefficient 5\n";
		}
		// keep air inlet node name
		else if($def_section == true&&preg_match('/.*!- Air Inlet Node Name.*/', $buffer, $matches)) {
            // keep the same line
		}
		// keep air outlet node name
		else if($def_section == true&&preg_match('/.*!- Air Outlet Node Name.*/', $buffer, $matches)) {
           // add a ventilation schedule 
		   $buffer = 	$buffer.
						"\nSchedule:Compact,\n". 
						"   VentilationSetback,                              ! Name\n".
						"   Fraction,                                        ! Type\n".
						"   Through: 12/31,                                  ! Type\n".
						"   For: Weekdays SummerDesignDay WinterDesignDay,   ! All days in year\n".
						"   Until: 07:00,\n".
						"   0.1,\n".
						"   Until: 19:00,\n".
						"   0.4,\n".
						"   Until: 24:00,\n".
						"   0.1,\n".
						"   For: Weekends,\n".
						"   Until: 24:00,\n".
						"   0.1,\n".
						"   For: AllOtherDays,\n".
						"   Until: 24:00,\n".
						"   0;\n";
			$def_section = false;
		} else if($def_section == true) { // if it is in the section, but does not match any cases above
			$buffer = "";                 // then delete the line 
		}
		
		// append the line to the file_target 
		if (fwrite($wh, $buffer) === FALSE) {
		   echo 'Download error: Cannot write to file ('.$file_target.')';
		   return true;            // return error
		}
    }
    fclose($rh);
    fclose($wh);

    copy($file_target, $file_source);

    // No error
    return false;
}

/*
 *  function: set the watts per zone floor area by percentage
 *  $file_source: the name of the input file, type=>string
 *  $file_target: the name of the output file, type=>string
 *  $percentage: the percentage of flow rate decreases(eg: $percentage=0.8 <=> decrease 20%), type=>number   
 */
function setEletricEquiment($file_source, $file_target, $percentage) {

	$header = "/.*ElectricEquipment,.*/";
    $rh = fopen($file_source, 'rb');
    $wh = fopen($file_target, 'wb');

	// section indicators 
	$def_section = false;

    if ($rh===false || $wh===false) {
		// error reading or opening file
		return true;
    }

    while (!feof($rh) && ($buffer = fgets($rh, 4096)) !== false) {

        // start of the section 
        $pattern = $header;
		if(preg_match($pattern, $buffer, $matches))
		{
			$def_section = true;
		}	

		// set the watts per zone floor area by the persentage
		if($def_section == true&&preg_match('/.*!- Watts per Zone Floor Area \{W\/m2\}.*/', $buffer, $matches)) {
            //echo "\noriginal: ", $buffer;
			$wpa = preg_replace('/\(0.[0-9]+\),.*/', '$1',$buffer)*$percentage;
            echo "modified: ", $buffer = "  ".$wpa.",                       !- Watts per Zone Floor Area {W/m2}\n";
		}

		// end of the section 
		$pattern = '/.*;.*/';
		if($def_section == true&&preg_match($pattern, $buffer, $matches))
		{
			$def_section = false;
		}

		// append the line to the file_target 
		if (fwrite($wh, $buffer) === FALSE) {
		   echo 'Download error: Cannot write to file ('.$file_target.')';
		   return true;            // return error
		}
    }
    fclose($rh);
    fclose($wh);

    copy($file_target, $file_source);

    // No error
    return false;
}

/*
 *  function: set the nominal thermal effciency by percentage, and nominal capacity
 *  $file_source: the name of the input file, type=>string
 *  $file_target: the name of the output file, type=>string
 *  $percentage: the percentage of nominal thermal efficiency (eg: $percentage=0.8 <=> decrease 20%), type=>number 
 * 	$boiler_cap: the capacity of the boiler 
 */
function setBoilerEff($file_source, $file_target, $percentage, $boiler_cap) {

	$header = "/.*Boiler Hot Water 1,.*!- Name.*/";
    $rh = fopen($file_source, 'rb');
    $wh = fopen($file_target, 'wb');

	// section indicators 
	$def_section = false;

    if ($rh===false || $wh===false) {
		// error reading or opening file
		return true;
    }

    while (!feof($rh) && ($buffer = fgets($rh, 4096)) !== false) {

        // start of the section 
        $pattern = $header;
		if(preg_match($pattern, $buffer, $matches))
		{
			$def_section = true;
		}	

		// set the nominal thermal efficiency by the persentage
		if($def_section == true&&preg_match('/.*!- Nominal Thermal Efficiency.*/', $buffer, $matches)) {
            //echo "\noriginal: ", $buffer;
			$thermal_eff = preg_replace('/\(0.[0-9]+\),.*/', '$1',$buffer)*$percentage;
            echo "modified: ", $buffer = "  ".$thermal_eff.",                       !- Nominal Thermal Efficiency\n";
		}
		
		// set the designed flow rate by the persentage
		if($def_section == true&&preg_match('/.* !- Nominal Capacity \{W\}.*/', $buffer, $matches)) {
            //echo "\noriginal: ", $buffer;
            echo "modified: ", $buffer = "  ".$boiler_cap.",                        !- Nominal Capacity {W}\n";
		}

		// end of the section 
		$pattern = '/.*;.*/';
		if($def_section == true&&preg_match($pattern, $buffer, $matches))
		{
			$def_section = false;
		}

		// append the line to the file_target 
		if (fwrite($wh, $buffer) === FALSE) {
		   echo 'Download error: Cannot write to file ('.$file_target.')';
		   return true;            // return error
		}
    }
    fclose($rh);
    fclose($wh);

    copy($file_target, $file_source);

    // No error
    return false;
}

/*
 *  function: remove the default lighting schedules
 *  $file_source: the name of the input file, type=>string
 *  $file_target: the name of the output file, type=>string
 */
function removeLightingSchedules($file_source, $file_target) {

	$header1 = "/Schedule:Day:Interval,$/";
	$header2 = "/Schedule:Week:Daily,$/";
	$header3 = "/Schedule:Year,$/";
	               
	$rh = fopen($file_source, 'rb');        // file header to read
    $wh = fopen($file_target, 'wb');        // file header to write
	
	// cases matching
	$case1 = false;	
	$case2 = false;
	$match = false;
	$not_match = false;
	
	$buffer = "";
	$line_counter = 0;

    if ($rh===false || $wh===false) {
		// error reading or opening file
		return true;
    }

    while (!feof($rh) && ($line = fgets($rh, 4096)) !== false) {

		
		// reset everything
		$pattern = '/.*;.*/';
		if(preg_match($pattern, $line, $matches))
		{
			if($match){
				//echo $line;
				$line = "\n";
			} 

			$buffer = "";	
			$line_counter = 0;
			$case1 = false;
			$case2 = false;
			$match = false;
			$not_match = false;
		}
		
		// match cases 
		if($match==true) {
			// remove the line
			//echo $line;
			$line="";
			
			// reset case 1 2
			$case1 = false;
			$case2 = false;
		}
	
		// case 1: check the first line, execute only once
		if($case1==true) {
			// check the first line for case 1
			if(preg_match('/.*Medium Office_Bldg_Light.*/', $line, $matches)) {		              
				$buffer="";
				$line="";
				$match = true;
			} else { 	
				// add buffer back to line
				$line = $buffer.$line;
				$buffer = "";
				$not_match = true;
			}
		}
		
		// not match cases
		if($not_match==true) {
			//echo $line;
			
		    // reset case 1 2
			$case1 = false;
			$case2 = false;
		}
		
		// case 2: check the second line, execute only once
		if($case2==true&&$line_counter==2) {
			// check the second line
			if(preg_match('/.*Medium Office_Bldg_Light.*/', $line, $matches)) {				
				$buffer="";
				$line="";
				$match = true;
			} else { 
				// add buffer back to line
				$line = $buffer.$line;
				$buffer = "";
				$not_match = true;
			}
		}
		
		// pre-case 2: iterate the first line
		if($case2==true&&$line_counter==1) {
		 
			$buffer = $buffer.$line;
			$line = "";
			$line_counter++;
		}

		// check case 1
		if(preg_match($header1, $line, $matches))
		{
			$case1 = true;
			//echo $line;
			$buffer = $line;
			$line = "";
		}	
		
		if(preg_match($header3, $line, $matches))
		{
			$case1 = true;
			//echo $line;
			$buffer = $line;
			$line = "";
		}
		
		// check case 2
		if(preg_match($header2, $line, $matches))
		{
			$case2 = true;
			//echo $line;
			$buffer = $line;
			$line = "";
			$line_counter++;
		}	

		//echo $line;
		// append the line to the file_target 
		if (fwrite($wh, $line) === FALSE) {
		   echo 'Download error: Cannot write to file ('.$file_target.')';
		   return true;            // return error
		}
    }
    fclose($rh);
    fclose($wh);

	copy($file_target, $file_source);
    // No error
    return false;
}

/*
 *  function: set the total cooling capacity, cop rates
 *  $file_source: the name of the input file, type=>string
 *  $file_target: the name of the output file, type=>string
 *  $cop: the percentage of COP rate (eg: $percentage=0.8 <=> decrease 20%), type=>number 
 * 	$cooling_cap: the capacity of the boiler 
 */
function setCondensingUnitEff($file_source, $file_target, $cop, $cooling_cap) {

	$header = "/.*Coil Cooling DX Two Speed 1,.*!- Name.*/";
    $rh = fopen($file_source, 'rb');
    $wh = fopen($file_target, 'wb');

	// section indicators 
	$def_section = false;

    if ($rh===false || $wh===false) {
		// error reading or opening file
		return true;
    }

    while (!feof($rh) && ($buffer = fgets($rh, 4096)) !== false) {

        // start of the section 
        $pattern = $header;
		if(preg_match($pattern, $buffer, $matches))
		{
			$def_section = true;
		}	

		// increases the high speed cop rate by percentage
		if($def_section == true&&preg_match('/.*!- Rated High Speed COP \{W\/W\}.*/', $buffer, $matches)) {
            //echo "\noriginal: ", $buffer;
			$cop_value = preg_replace('/\(0.[0-9]+\),.*/', '$1',$buffer)*$cop;
            echo "modified: ", $buffer = "  ".$cop_value.",                       !- Rated High Speed COP {W/W}\n";
		}
		
		// autosize the high speed air flow rate
		if($def_section == true&&preg_match('/.*!- Rated High Speed Air Flow Rate \{m3\/s\}.*/', $buffer, $matches)) {
            echo "modified: ", $buffer = "  Autosize,                       !- Rated High Speed Air Flow Rate {m3/s}\n";
		}
		
		// set the designed flow rate by the persentage
		if($def_section == true&&preg_match('/.* !- Rated High Speed Total Cooling Capacity \{W\}.*/', $buffer, $matches)) {
            //echo "\noriginal: ", $buffer;
            echo "modified: ", $buffer = "  ".$cooling_cap.",                        !- Nominal Capacity {W}\n";
		}

		// end of the section 
		$pattern = '/.*;.*/';
		if($def_section == true&&preg_match($pattern, $buffer, $matches))
		{
			$def_section = false;
		}

		// append the line to the file_target 
		if (fwrite($wh, $buffer) === FALSE) {
		   echo 'Download error: Cannot write to file ('.$file_target.')';
		   return true;            // return error
		}
    }
    fclose($rh);
    fclose($wh);

    copy($file_target, $file_source);

    // No error
    return false;
}



/*
 *  function: add a new measure field at the end of idf file 
 *  $file_source: input file name, type=>string
 *  $file_target: output file name, type=>string
 *  $added_str: body of field in idf file, type=>string 
 */
function addMeasure($file_source, $file_target, $added_str) {

    $rh = fopen($file_source, 'rb');
    $wh = fopen($file_target, 'wb');

	// section indicators 
	$def_section = false;


    if ($rh===false || $wh===false) {
		// error reading or opening file
		return true;
    }

    while (!feof($rh) && ($buffer = fgets($rh, 4096)) !== false) {

        // start of the section of lighting default schedule
        $pattern = $header;
		if(preg_match($pattern, $buffer, $matches))
		{
			$def_section = true;
		}	

		// replace the default lighting schedule
		if($def_section == true&&!preg_match('/.*;.*/', $buffer, $matches)) {
			$buffer = '';
		}

		// end of the section of electric equipment  
		$pattern = '/.*;.*/';
		if($def_section == true&&preg_match($pattern, $buffer, $matches))
		{

			$buffer = $replace_str;
			$def_section = false;
		}

		// write to file_target 
		if (fwrite($wh, $buffer) === FALSE) {
		   // 'Download error: Cannot write to file ('.$file_target.')';
		   return true;
		}
    }
    fclose($rh);
    fclose($wh);

    copy($file_target, $file_source);

    // No error
    return false;
}

$installationCost = 0;                                // the cost of installation
$cur_model = $_SESSION['cur_model']="eem_{$version}"; // the current model
$selectedValues = $_POST['selected'];                 // The measure(s) that is/are ready to be installed 

// iterate the selected measure list in order to installation
for( $cnt = 0; $cnt < count($selectedValues); $cnt++)  {

    $measureCost = 0;     // the cost of the measure

    switch ($selectedValues[$cnt]) {
        case 'oblsChecked':
####=======================================================================================================================###############
# OCCUPANCY SENSING
setMeasure($file_in, $file_out, '/.*Medium Office_Bldg_Light Default Schedule,.*!- Name.*/', "    Medium Office_Bldg_Light Default Schedule,  !- Name
    Fraction,                !- Schedule Type Limits Name
    No,                      !- Interpolate to Timestep
    07:00,                   !- Time 1
    0.05,                    !- Value Until Time 1
    08:00,                   !- Time 2
    0.2,                     !- Value Until Time 2
    09:00,                   !- Time 3
    0.33,                     !- Value Until Time 3
    10:00,                   !- Time 4
    0.39,                     !- Value Until Time 4
    12:00,                   !- Time 5
    0.42,                     !- Value Until Time 5
    13:00,                   !- Time 6
    0.4,                     !- Value Until Time 6
    15:00,                   !- Time 7
    0.42,                     !- Value Until Time 7
    16:00,                   !- Time 8
    0.4,                     !- Value Until Time 8
    17:00,                   !- Time 9
    0.38,                    !- Value Until Time 9
    18:00,                   !- Time 10
    0.32,                    !- Value Until Time 10
    19:00,                   !- Time 11
    0.23,                    !- Value Until Time 11
    20:00,                   !- Time 12
    0.18,                    !- Value Until Time 12
    22:00,                   !- Time 13
    0.16,                    !- Value Until Time 13
    24:00,                   !- Time 14
    0.05;                    !- Value Until Time 14\n");

# LIGHTING Rule Day 1 SCHEDULE
setMeasure($file_in, $file_out, '/.*Medium Office_Bldg_Light Rule 1 Day Schedule,.*!- Name.*/', "    Medium Office_Bldg_Light Rule 1 Day Schedule,  !- Name
    Fraction,                !- Schedule Type Limits Name
    No,                      !- Interpolate to Timestep
    24:00,                   !- Time 1
    0.05;                    !- Value Until Time 1\n");


# LIGHTING Rule Day 2 SCHEDULE
setMeasure($file_in, $file_out, '/.*Medium Office_Bldg_Light Rule 2 Day Schedule,.*!- Name.*/', "    Medium Office_Bldg_Light Rule 2 Day Schedule,  !- Name
    Fraction,                !- Schedule Type Limits Name
    No,                      !- Interpolate to Timestep
    24:00,                   !- Time 1
    0.05;                    !- Value Until Time 1\n");

                $_SESSION['measureFinished']['oblsChecked'] = 'finished';
                echo "<br>!!!!!!!!!!!!!!!!! oblsChecked !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!<br> ";
                $measureCost = 50000;
            break;

          
		case 'emergencyLightingChecked':
####=======================================================================================================================###############
# EMERGENCY LIGHTING SCHEDULE
setMeasure($file_in, $file_out, '/.*Medium Office_Bldg_Light Default Schedule,.*!- Name.*/', "    Medium Office_Bldg_Light Default Schedule,  !- Name
    Fraction,                !- Schedule Type Limits Name
    No,                      !- Interpolate to Timestep
    07:00,                   !- Time 1
    0.10,                    !- Value Until Time 1
    08:00,                   !- Time 2
    0.17,                     !- Value Until Time 2
    09:00,                   !- Time 3
    0.30,                     !- Value Until Time 3
    10:00,                   !- Time 4
    0.36,                     !- Value Until Time 4
    12:00,                   !- Time 5
    0.39,                     !- Value Until Time 5
    13:00,                   !- Time 6
    0.37,                     !- Value Until Time 6
    15:00,                   !- Time 7
    0.39,                     !- Value Until Time 7
    16:00,                   !- Time 8
    0.37,                     !- Value Until Time 8
    17:00,                   !- Time 9
    0.35,                    !- Value Until Time 9
    18:00,                   !- Time 10
    0.29,                    !- Value Until Time 10
    19:00,                   !- Time 11
    0.20,                    !- Value Until Time 11
    20:00,                   !- Time 12
    0.15,                    !- Value Until Time 12
    22:00,                   !- Time 13
    0.13,                    !- Value Until Time 13
    24:00,                   !- Time 14
    0.10;                    !- Value Until Time 14\n");

# LIGHTING Rule Day 1 SCHEDULE
setMeasure($file_in, $file_out, '/.*Medium Office_Bldg_Light Rule 1 Day Schedule,.*!- Name.*/', "    Medium Office_Bldg_Light Rule 1 Day Schedule,  !- Name
    Fraction,                !- Schedule Type Limits Name
    No,                      !- Interpolate to Timestep
    24:00,                   !- Time 1
    0.09;                    !- Value Until Time 1\n");


# LIGHTING Rule Day 2 SCHEDULE
setMeasure($file_in, $file_out, '/.*Medium Office_Bldg_Light Rule 2 Day Schedule,.*!- Name.*/', "    Medium Office_Bldg_Light Rule 2 Day Schedule,  !- Name
    Fraction,                !- Schedule Type Limits Name
    No,                      !- Interpolate to Timestep
    24:00,                   !- Time 1
    0.09;                    !- Value Until Time 1\n");

                $_SESSION['measureFinished']['emergencyLightingChecked'] = 'finished';
                echo "<br>!!!!!!!!!!!!!!!!! emergencyLightingChecked !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!<br> ";
                $measureCost = 40000;
            break;

             case 'plugLoadChecked':
####=======================================================================================================================###############
# Plug Load Control
setMeasure($file_in, $file_out, '/.*Medium Office_Bldg_Equip Default Schedule,.*!- Name.*/', "    Medium Office_Bldg_Equip Default Schedule,  !- Name
    Fraction,                !- Schedule Type Limits Name
    No,                      !- Interpolate to Timestep
    07:00,                   !- Time 1
    0.25,                     !- Value Until Time 1
    8:00,                   !- Time 2
    0.57,                     !- Value Until Time 2
    9:00,                   !- Time 3
    0.65,                     !- Value Until Time 3
    10:00,                   !- Time 4
    0.78,                     !- Value Until Time 4
    12:00,                   !- Time 5
    0.82,                     !- Value Until Time 5
    13:00,                   !- Time 6
    0.8,                     !- Value Until Time 6
    16:00,                   !- Time 7
    0.82,                     !- Value Until Time 7
    17:00,                   !- Time 8
    0.79,                     !- Value Until Time 8
    18:00,                   !- Time 9
    0.66,                     !- Value Until Time 9
    19:00,                   !- Time 10
    0.6,                     !- Value Until Time 10
    20:00,                   !- Time 11
    0.52,                     !- Value Until Time 11
    24:00,                   !- Time 12
    0.25;                     !- Value Until Time 12\n");

# EQUIP Rule Day 1 SCHEDULE
setMeasure($file_in, $file_out, '/.*Medium Office_Bldg_Equip Rule 1 Day Schedule,.*!- Name.*/', "    Medium Office_Bldg_Equip Rule 1 Day Schedule,  !- Name
    Fraction,                !- Schedule Type Limits Name
    No,                      !- Interpolate to Timestep
    24:00,                   !- Time 1
    0.25;                    !- Value Until Time 1\n");

# EQUIP Rule Day 2 SCHEDULE
setMeasure($file_in, $file_out, '/.*Medium Office_Bldg_Equip Rule 2 Day Schedule,.*!- Name.*/', "    Medium Office_Bldg_Equip Rule 2 Day Schedule,  !- Name
    Fraction,                !- Schedule Type Limits Name
    No,                      !- Interpolate to Timestep
    24:00,                   !- Time 1
    0.25;                    !- Value Until Time 1\n");

                  echo "<br>!!!!!!!!!!!!!!!!! plugLoadChecked !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!<br> ";
                  $_SESSION['measureFinished']['plugLoadChecked'] = 'finished';
                  $measureCost = 60000;

              break;

              case 'OfficefixturedChecked':
####=======================================================================================================================###############
# LIGHTING POWER DENSITY - OFFICE FIXTURE
setMeasure($file_in, $file_out, '/.*Lights,.*/', "  Lights,
    ASHRAE_90.1-2004 ClimateZone 1-8 MediumOffice WholeBuilding Lights,  !- Name
    ASHRAE_90.1-2004 ClimateZone 1-8 MediumOffice WholeBuilding,  !- Zone or ZoneList Name
    Medium Office_Bldg_Light,!- Schedule Name
    Watts/Area,              !- Design Level Calculation Method
    ,                        !- Lighting Level {W}
    17.06279,                !- Watts per Zone Floor Area {W/m2}
    ,                        !- Watts per Person {W/person}
    ,                        !- Return Air Fraction
    0.42,                    !- Fraction Radiant
    0.18,                    !- Fraction Visible
    1;                       !- Fraction Replaceable\n");

                    echo "<br>!!!!!!!!!!!!!!!!! OfficeFixturedChecked !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!<br> ";
                    $_SESSION['measureFinished']['OfficefixturedChecked'] = 'finished';
                    $measureCost = 200000;
                break;

              case 'bathroomFixturedChecked':
####=======================================================================================================================###############
# LIGHTING POWER DENSITY - BATHROOM
setMeasure($file_in, $file_out, '/.*Lights,.*/', "  Lights,
    ASHRAE_90.1-2004 ClimateZone 1-8 MediumOffice WholeBuilding Lights,  !- Name
    ASHRAE_90.1-2004 ClimateZone 1-8 MediumOffice WholeBuilding,  !- Zone or ZoneList Name
    Medium Office_Bldg_Light,!- Schedule Name
    Watts/Area,              !- Design Level Calculation Method
    ,                        !- Lighting Level {W}
    21.34795,                !- Watts per Zone Floor Area {W/m2}
    ,                        !- Watts per Person {W/person}
    ,                        !- Return Air Fraction
    0.42,                    !- Fraction Radiant
    0.18,                    !- Fraction Visible
    1;                       !- Fraction Replaceable\n");

                    echo "<br>!!!!!!!!!!!!!!!!! bathroomFixturedChecked !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!<br> ";
                    $_SESSION['measureFinished']['bathroomFixturedChecked'] = 'finished';
                    $measureCost = 8000;
                break;

                 case 'energyStarEquipmentChecked':
####=======================================================================================================================###############
# EnergyStar Equipment
setEletricEquiment($file_in, $file_out, 0.85);

             echo "<br>!!!!!!!!!!!!!!!!! energyStarEquipmentChecked !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!<br> ";
             $_SESSION['measureFinished']['energyStarEquipmentChecked'] = 'finished';
             $measureCost = 15000;          
 
                    break;
					
                     

                    case 'bmsSBChecked':
####=======================================================================================================================###############
# BUILDING MANAGEMENT SYSTEM
# HEATING SETPOINT SCHEDULE
setMeasure($file_in, $file_out, '/.*Heating Sch Default,.*!- Name.*/', "    Heating Sch Default,     !- Name
    Temperature 2,           !- Schedule Type Limits Name
    No,                      !- Interpolate to Timestep
    7:00,                   !- Time 1
    12,                      !- Value Until Time 1
    19:00,                   !- Time 2
    16,                      !- Value Until Time 2
    24:00,                   !- Time 3
    12;                      !- Value Until Time 3\n");

# COOLING SETPOINT SCHEDULE
setMeasure($file_in, $file_out, '/.*Cooling Sch Default,.*!- Name.*/', "    Cooling Sch Default,     !- Name
    Temperature 3,           !- Schedule Type Limits Name
    No,                      !- Interpolate to Timestep
    7:00,                    !- Time 1
    35,                      !- Value Until Time 1
    19:00,                   !- Time 2
    27,                      !- Value Until Time 2
    24:00,                   !- Time 3
    35;                      !- Value Until Time 3\n");

# Ventilation Setback
setVentilation($file_in, $file_out);


            $_SESSION['measureFinished']['bmsSBChecked'] = 'finished';
            echo "<br>!!!!!!!!!!!!!!!!! bmsSBChecked !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!<br> "; 
                        $measureCost = 50000;
                        break;

                        case 'enclosureRecommisChecked':
####=======================================================================================================================###############
# INFILTRATION:  ENCLOSURE RECOMMISSIONING MEASURE
setInfiltration($file_in, $file_out, 0.9);


                             echo "<br>!!!!!!!!!!!!!!!!! enclosureRecommisChecked !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!<br> ";
                             $_SESSION['measureFinished']['enclosureRecommisChecked'] = 'finished';
                             $measureCost = 25000;
                        break;



                     case 'doorWeatherizationChecked':
####=======================================================================================================================###############
# INFILTRATION - WEATHERIZATION
							setInfiltration($file_in, $file_out, 0.85);


             				echo "<br>!!!!!!!!!!!!!!!!! doorWeatherizationChecked !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!<br> ";
							$_SESSION['measureFinished']['doorWeatherizationChecked'] = 'finished';
							$measureCost = 5000;
                        break;

                        case 'wallInsulation1Checked':
####=======================================================================================================================###############
# WALL CONSTRUCTION:
# THE R-10 MEASURE
setMeasure($file_in, $file_out, '/.*Wall Insulation \[39\],.*!- Name.*/', "    Wall Insulation [39],    !- Name
    MediumRough,             !- Roughness
    0.20,                 !- Thickness {m}
    0.035,                   !- Conductivity {W/m-K}
    265,                     !- Density {kg/m3}
    836.8,                   !- Specific Heat {J/kg-K}
    0.9,                     !- Thermal Absorptance
    0.7,                     !- Solar Absorptance
    0.7;                     !- Visible Absorptance\n");

//setMeasure($file_in, $file_out, '/.*ASHRAE_189.1-2009_ExtWall_SteelFrame_ClimateZone 4-8,.*!- Name.*/', "    ASHRAE_189.1-2009_ExtWall_SteelFrame_ClimateZone 4-8,  !- Name
//    Wall Insulation [39],   !- Outside Layer
//    Bldg101BrickExtWall;    !- Layer 1\n");

                            echo "<br>!!!!!!!!!!!!!!!!! wallInsulation1Checked !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!<br> ";
                            $_SESSION['measureFinished']['wallInsulation1Checked'] = 'finished';
                            $measureCost = 100000;

                        break;

                        case 'wallInsulation2Checked':
//setMeasure($file_in, $file_out, '/.*ASHRAE_189.1-2009_ExtWall_SteelFrame_ClimateZone 4-8,.*!- Name.*/', "    ASHRAE_189.1-2009_ExtWall_SteelFrame_ClimateZone 4-8,  !- Name
//    Wall Insulation [39],   !- Outside Layer
//    Bldg101BrickExtWall;    !- Layer 1\n");

# R-20 MEASURE
setMeasure($file_in, $file_out, '/.*Wall Insulation \[39\],.*!- Name.*/', "    Wall Insulation [39],    !- Name
    MediumRough,             !- Roughness
    0.30,                  !- Thickness {m}
    0.03,                   !- Conductivity {W/m-K}
    265,                     !- Density {kg/m3}
    836.8,                   !- Specific Heat {J/kg-K}
    0.9,                     !- Thermal Absorptance
    0.7,                     !- Solar Absorptance
    0.7;                     !- Visible Absorptances\n");

                              echo "<br>!!!!!!!!!!!!!!!!! wallInsulation2Checked !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!<br> ";
                              $_SESSION['measureFinished']['wallInsulation2Checked'] = 'finished';
                              $measureCost = 130000;
                        break;

                        case 'roofInsulationChecked':
####=======================================================================================================================###############
# ROOF INSULATION
#setMeasure($file_in, $file_out, '/.*ZoneInfiltration:DesignFlowRate,.*/', "ZoneInfiltration:DesignFlowRate,
#    ASHRAE_90.1-2004 ClimateZone 1-8 MediumOffice WholeBuilding Infiltration,  !- Name

# ROOF CONSTRUCTION
//setMeasure($file_in, $file_out, '/.*Glass fibre\/wool - wool at 10C degrees_0.192303400000769,.*!- Material name.*/', "Glass fibre/wool - wool at 10C degrees_0.192303400000769,  !- Material name
setMeasure($file_in, $file_out, '/.*Roof Insulation \[21\],.*!- Name.*/', "Roof Insulation [21],                   !- Name
  MediumRough,                            !- Roughness
  0.541,                                 !- Thickness {m}
  0.039,                                  !- Conductivity {W/m-K}
  265,                                    !- Density {kg/m3}
  836.8,                                  !- Specific Heat {J/kg-K}
  0.9,                                    !- Thermal Absorptance
  0.7,                                    !- Solar Absorptance
  0.7;                                    !- Visible Absorptance\n");

                            echo "<br>!!!!!!!!!!!!!!!!! roofInsulationChecked !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!<br> ";
                            $_SESSION['measureFinished']['roofInsulationChecked'] = 'finished';
                            $measureCost = 50000;

                        break;

                        case 'windowsUpgradelChecked':
####=======================================================================================================================###############
# WINDOW CONSTRUCTION: WINDOW REPLACEMENT
setMeasure($file_in, $file_out, '/.*Theoretical Glass \[207\],.*!- Name.*/', "Theoretical Glass [207],                !- Name
  SpectralAverage,                        !- Optical Data Type
  ,                                       !- Window Glass Spectral Data Set Name
  0.009,                                  !- Thickness {m}
  0.3311,                                 !- Solar Transmittance at Normal Incidence
  0.6189,                                 !- Front Side Solar Reflectance at Normal Incidence
  0.6189,                                 !- Back Side Solar Reflectance at Normal Incidence
  0.44,                                   !- Visible Transmittance at Normal Incidence
  0.51,                                   !- Front Side Visible Reflectance at Normal Incidence
  0.51,                                   !- Back Side Visible Reflectance at Normal Incidence
  0,                                      !- Infrared Transmittance at Normal Incidence
  0.9,                                    !- Front Side Infrared Hemispherical Emissivity
  0.9,                                    !- Back Side Infrared Hemispherical Emissivity
  0.0133,                                 !- Conductivity {W/m-K}
  1,                                      !- Dirt Correction Factor for Solar and Visible Transmittance
  No;                                     !- Solar Diffusing\n");


                             echo "<br>!!!!!!!!!!!!!!!!! windowsUpgradelChecked !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!<br> ";
                             $_SESSION['measureFinished']['windowsUpgradelChecked'] = 'finished';
                             $measureCost = 100000;
                        break;

       case 'windowsFilmChecked':
####=======================================================================================================================###############
# WINDOW CONSTRUCTION: WINDOW FILM
setMeasure($file_in, $file_out, '/.*Theoretical Glass \[207\],.*!- Name.*/', "Theoretical Glass [207],                !- Name
  SpectralAverage,                        !- Optical Data Type
  ,                                       !- Window Glass Spectral Data Set Name
  0.004,                                  !- Thickness {m}
  0.3311,                                 !- Solar Transmittance at Normal Incidence
  0.6189,                                 !- Front Side Solar Reflectance at Normal Incidence
  0.6189,                                 !- Back Side Solar Reflectance at Normal Incidence
  0.44,                                   !- Visible Transmittance at Normal Incidence
  0.51,                                   !- Front Side Visible Reflectance at Normal Incidence
  0.51,                                   !- Back Side Visible Reflectance at Normal Incidence
  0,                                      !- Infrared Transmittance at Normal Incidence
  0.9,                                    !- Front Side Infrared Hemispherical Emissivity
  0.9,                                    !- Back Side Infrared Hemispherical Emissivity
  0.0133,                                 !- Conductivity {W/m-K}
  1,                                      !- Dirt Correction Factor for Solar and Visible Transmittance
  No;                                     !- Solar Diffusing\n");


							echo "<br>!!!!!!!!!!!!!!!!! windowsFilmChecked !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!<br> ";
							$_SESSION['measureFinished']['windowsFilmChecked'] = 'finished';
							$measureCost = 25000;
                        break;

                        case 'sysEffChecked':
####=======================================================================================================================###############
# Condensing Unit Replacement
// get condensing unit sizing
$sql_file1="eem/{$user_dir}/Output/eem_{$pre_version}.sql";
$eeb0 = new EEB_SQLITE3("$sql_file1");
$cc = $eeb0->getValues('EquipmentSummary', 'Entire Facility', 'Cooling Coils','%');
print_r($cc);
// convert btu per hour to watt
$unit_cap = $cc['COIL COOLING DX TWO SPEED 1']['Nominal Total Capacity']*0.29307107;

							setCondensingUnitEff($file_in, $file_out, 1.30, $unit_cap);


             				echo "<br>!!!!!!!!!!!!!!!!! sysEffChecked !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!<br> ";
							$_SESSION['measureFinished']['sysEffChecked'] = 'finished';
							$addCondensingUnitCost = true;
                        break;


						case 'condensingBoilerChecked':
####=======================================================================================================================###############
# BOILER CAPACITY AND EFFICIENCY
// get nominal capacity in btu/hr
$sql_file1="eem/{$user_dir}/Output/eem_{$pre_version}.sql";
$eeb0 = new EEB_SQLITE3("$sql_file1");
$cp = $eeb0->getValues('EquipmentSummary', 'Entire Facility', 'Central Plant','%');

// convert btu per hour to watt
$boiler_cap = $cp['BOILER HOT WATER 1']['Nominal Capacity']*0.29307107;

setBoilerEff($file_in, $file_out, 1.15, $boiler_cap);

		         echo "<br>!!!!!!!!!!!!!!!!! condensingBoilerChecked !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!<br> ";
		         $_SESSION['measureFinished']['condensingBoilerChecked'] = 'finished';
		         $addCondensingBoilerCost = true;

             break;

case 'airEconChecked':
####=======================================================================================================================###############
# OUTDOOR AIR ECNOMIZER
setMeasure($file_in, $file_out, '/.*Controller Outdoor Air 1,.*!- Name,$/', "  Controller Outdoor Air 1,               !- Name
  Node 37,                                !- Relief Air Outlet Node Name
  Node 31,                                !- Return Air Node Name
  Node 38,                                !- Mixed Air Node Name
  Node 36,                                !- Actuator Node Name
  Autosize,                               !- Minimum Outdoor Air Flow Rate {m3/s}
  Autosize,                               !- Maximum Outdoor Air Flow Rate {m3/s}
  FixedDryBulb,                           !- Economizer Control Type
  ModulateFlow,                           !- Economizer Control Action Type
  28,                                     !- Economizer Maximum Limit Dry-Bulb Temperature {C}
  64000,                                  !- Economizer Maximum Limit Enthalpy {J/kg}
  ,                                       !- Economizer Maximum Limit Dewpoint Temperature {C}
  ,                                       !- Electronic Enthalpy Limit Curve Name
  -100,                                   !- Economizer Minimum Limit Dry-Bulb Temperature {C}
  NoLockout,                              !- Lockout Type
  FixedMinimum,                           !- Minimum Limit Type
  ,                                       !- Minimum Outdoor Air Schedule Name
  ,                                       !- Minimum Fraction of Outdoor Air Schedule Name
  ,                                       !- Maximum Fraction of Outdoor Air Schedule Name
  Controller Mechanical Ventilation 1,    !- Mechanical Ventilation Controller Name
  ,                                       !- Time of Day Economizer Control Schedule Name
  No,                                     !- High Humidity Control
  ,                                       !- Humidistat Control Zone Name
  ,                                       !- High Humidity Outdoor Air Flow Ratio
  Yes,                                    !- Control High Indoor Humidity Based on Outdoor Humidity Ratio
  BypassWhenOAFlowGreaterThanMinimum;     !- Heat Recovery Bypass Control Type\n");
             
				echo "<br>!!!!!!!!!!!!!!!!! airEconChecked !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!<br> ";
           		$_SESSION['measureFinished']['airEconChecked'] = 'finished';
                      
                        $addAirEconCost = true;        
            


           	break;

case 'daylightDimmingChecked':
####=======================================================================================================================###############
# DAYLIGHT DIMMING

//echo "something";
// remove the default lighting schedule
removeLightingSchedules($file_in, $file_out);

//echo "something";
$dayLightDiming = "\nSchedule:File,
   Medium Office_Bldg_Light,                 !- Name

   Any Number,                               !- ScheduleType
   powerSchedule.csv,                        !- Name of File
   4,                                        !- Column Number
   0,                                        !- Rows to Skip at Top
   8760,                                     !- Number of Hours of Data
   Comma;                                    !- Column Separator\n
ScheduleTypeLimits,
   Any Number;                               !- Not Limited\n";

			// add the strings to the end of file
			$fh = fopen($file_in, 'a') or die("can't open file");
			fwrite($fh, $dayLightDiming);
			fclose($fh);
			
			//echo "<br>";
			// run Daysim 
			require 'newDaysimFiles.php';
            //echo "<br>DaySim finished<br>";
			//echo "Where am I ?", `pwd`;
			//echo "END ?";

			 $_SESSION['measureFinished']['daylightDimmingChecked'] = 'finished';
             echo "<br>!!!!!!!!!!!!!!!!! daylightDimmingChecked !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!<br> ";
             $measureCost = 75000;
 
			break;

        default:
            # code...
            break;
    }  // end of switch statement

    $installationCost = $installationCost + $measureCost;  // increase the installation cost

}  // end of for-loop


copy($file_in, $file_save);  // save a copy of eem version 


$temp_dir="eem/$user_dir";
if(!chdir($temp_dir)) { echo "Can't find this dirctory"; return 1; }     // go to eeb_rb folder
	
$cmd = "runenergyplus eem_{$version}.idf /usr/local/EnergyPlus-8-0-0/WeatherData/USA_PA_Philadelphia.Intl.AP.724080_TMY3.epw";
$run = shell_exec($cmd);   # run the simulation

# add the new version EEM model to the list
$_SESSION['Model'][$version] = "eem_{$version}";

// udpate the latest version
$cur_model = "eem_{$version}"; //assign the current model

// baseline energy cost
$baselineCost = $_SESSION['newCost'][0];

// the current year from 1 to 10 
$current_yr = ++$_SESSION['current_yr'];

// add a new energy simulation cost
$sql_file="Output/{$cur_model}.sql";
$eeb = new EEB_SQLITE3("$sql_file");
$electric_tariff = $eeb->getValues('Tariff Report', 'BLDG101_ELECTRIC_RATE', 'Categories', '%');
$gas_tariff = $eeb->getValues('Tariff Report', 'BLDG101_GAS_RATE',  'Categories', '%');
$newCost = $_SESSION['newCost'][$current_yr] = round($electric_tariff['Total (~~$~~)'][Sum] + $gas_tariff['Total (~~$~~)'][Sum],2);


$_SESSION['cur_model']="eem_{$version}"; 

// add a new installation cost
if($addAirEconCost) {

    // get the air economizer data from Fans report table
    $fans = $eeb->getValues('EquipmentSummary', 'Entire Facility', 'Fans','%');

    $airEconUnit = 0.5;                                   // air economizer unit ($/CFM)
    $measureCost = $fans['FAN VARIABLE VOLUME 1']['Max Air Flow Rate'] * $airEconUnit; 
    $installationCost += $measureCost;
} 

if($addCondensingBoilerCost) {

    // get the water boiler data from Central Plant report table
    $centralPlant = $eeb->getValues('EquipmentSummary', 'Entire Facility', 'Central Plant','%');

    $boilerUnit = 25;                    // condensing boilder unit ($/MBH)
    $measureCost = $centralPlant['BOILER HOT WATER 1']['Nominal Capacity'] * $boilerUnit / 1000;
    $installationCost += $measureCost;
}

if($addCondensingUnitCost) {

    // get the data from Cooling Coils report table 
    $coolingCoils = $eeb->getValues('EquipmentSummary', 'Entire Facility', 'DX Cooling Coils', '%');

    $coilsUnit = 1250;          // condensing cooling coils unit ($/ton)
    $measureCost = $coolingCoils['COIL COOLING DX TWO SPEED 1']['Standard Rated Net Cooling Capacity'] * $coilsUnit / 1000;
    $installationCost += $measureCost;
}

$_SESSION['installationCost'][$current_yr] = round($installationCost,2);               

$availableCap = $_SESSION['availableCap'][$current_yr];   // the current available capacity

// add a new remaining cap
$remainingCap = $_SESSION['remainingCap'][$current_yr] = round($availableCap - $installationCost,2); 

// add a new cumulated saving
$cumulatedSaving = $_SESSION['cumulatedSaving'][$current_yr] = round($baselineCost - $newCost + $_SESSION['cumulatedSaving'][$current_yr-1],2);

// add a new (remaining cap + cumulated saving)
$_SESSION['remainingCapPlusCumulatedSaving'][$current_yr] = round($remainingCap + $cumulatedSaving,2);

// add a new 3% interest rate comparision
$_SESSION['3PercentInterestRate'][$current_yr] =  round($_SESSION['remainingCapPlusCumulatedSaving'][$current_yr-1] *1.03 + 100000,2);

// add a new percentage saving
$_SESSION['percentageSaving'][$current_yr] = round((1 - $newCost / $baselineCost)*100, 1);

// add a new available cap
$_SESSION['availableCap'][$current_yr+1] = round($_SESSION['remainingCap'][$current_yr] + 100000,2); 


// reroute to tracking-sheet.php page
header("location: http://rmt.eebhub.org/comprehensive2/tracking-sheet.php");        

?>
