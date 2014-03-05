<?php
require 'php/EEB_SQLITE3.php';
require 'php/EEB_UI.php';
session_start();

$ui = new EEB_UI;   // default user interface
$user_dir = $_SESSION[user_dir]; // user directory
$baseline_model = $_SESSION[Model][0];

if($_POST['Model'][0]==NULL) {
	$_POST['Model'][0]=$baseline_model;
}
if($_POST['Model'][1]==NULL) {
  $_POST['Model'][1]=$baseline_model;
}
if($_POST['Model'][2]==NULL) {
  $_POST['Model'][2]=$baseline_model;
}
if($_POST['Model'][3]==NULL) {
  $_POST['Model'][3]=$baseline_model;
}

// define the sql file path
/*
if ($_POST['num_package'] != NULL) {
	$cur_model = $_SESSION['cur_model'] = $_POST['num_package'];
} elseif($_POST['num_package'] == NULL && $_SESSION['cur_model'] == NULL) {
	$cur_model = $_SESSION['Model'][0];
} else {
	$cur_model = $_SESSION['cur_model'];
}*/

if($_POST[Model][0] != $baseline_model){
  $sql_file0="eem/{$user_dir}/Output/{$_POST[Model][0]}.sql";
} else {
  $sql_file0="ENERGYPLUS/idf/{$_SESSION[Model][0]}/EnergyPlusPreProcess/EnergyPlus-0/eplusout.sql";
}
if($_POST[Model][1] != $baseline_model){
  $sql_file1="eem/{$user_dir}/Output/{$_POST[Model][1]}.sql";
} else {
  $sql_file1="ENERGYPLUS/idf/{$_SESSION[Model][0]}/EnergyPlusPreProcess/EnergyPlus-0/eplusout.sql";
}
if($_POST[Model][2] != $baseline_model){
  $sql_file2="eem/{$user_dir}/Output/{$_POST[Model][2]}.sql";
} else {
  $sql_file2="ENERGYPLUS/idf/{$_SESSION[Model][0]}/EnergyPlusPreProcess/EnergyPlus-0/eplusout.sql";
}
if($_POST[Model][3] != $baseline_model){
  $sql_file3="eem/{$user_dir}/Output/{$_POST[Model][3]}.sql";
} else {
  $sql_file3="ENERGYPLUS/idf/{$_SESSION[Model][0]}/EnergyPlusPreProcess/EnergyPlus-0/eplusout.sql";
}

/*
$sql_file1="eem/{$user_dir0}{$_POST['Model'][0]}.idf/EnergyPlusPreProcess/EnergyPlus-0/eplusout.sql";
$sql_file2="eem/{$user_dir1}{$_POST['Model'][1]}.idf/EnergyPlusPreProcess/EnergyPlus-0/eplusout.sql";
$sql_file3="eem/{$user_dir2}{$_POST['Model'][2]}.idf/EnergyPlusPreProcess/EnergyPlus-0/eplusout.sql";
$sql_file4="eem/{$user_dir3}{$_POST['Model'][3]}.idf/EnergyPlusPreProcess/EnergyPlus-0/eplusout.sql";
*/

$sql_file="eem/{$user_dir}/Output/$_SESSION[cur_model].sql";

#$eeb = new EEB_SQLITE3('php/modified_V8_V720.sql');
$baseline = new EEB_SQLITE3("./$sql_file0");
$eem1 = new EEB_SQLITE3("./$sql_file1");
$eem2 = new EEB_SQLITE3("./$sql_file2");
$eem3 = new EEB_SQLITE3("./$sql_file3");

# Baseline data
$base_e_vals = $baseline->getValuesByCategory('END USE ENERGY CONSUMPTION ELECTRICITY MONTHLY', 'Meter', '', '%');
$baseline_value[Jan] = array_sum($base_e_vals["January"]);
$baseline_value[Feb] = array_sum($base_e_vals["February"]);
$baseline_value[Mar] = array_sum($base_e_vals["March"]);
$baseline_value[Apr] = array_sum($base_e_vals["April"]);
$baseline_value[May] = array_sum($base_e_vals["May"]);
$baseline_value[Jun] = array_sum($base_e_vals["June"]);
$baseline_value[Jul] = array_sum($base_e_vals["July"]);
$baseline_value[Aug] = array_sum($base_e_vals["August"]);
$baseline_value[Sep] = array_sum($base_e_vals["September"]);
$baseline_value[Oct] = array_sum($base_e_vals["October"]);
$baseline_value[Nov] = array_sum($base_e_vals["November"]);
$baseline_value[Dec] = array_sum($base_e_vals["December"]);

$baseline_data = "[".convertToDataString($baseline_value, NULL, NULL, NULL)."]";
//print_r($baseline_data);

# EEM data
$base2_e_vals = $baseline->getValuesByMonthly('END USE ENERGY CONSUMPTION ELECTRICITY MONTHLY', 'Meter', '', '%');
$eem1_e_vals = $eem1->getValuesByMonthly('END USE ENERGY CONSUMPTION ELECTRICITY MONTHLY', 'Meter', '', '%');
$eem2_e_vals = $eem2->getValuesByMonthly('END USE ENERGY CONSUMPTION ELECTRICITY MONTHLY', 'Meter', '', '%');
$eem3_e_vals = $eem3->getValuesByMonthly('END USE ENERGY CONSUMPTION ELECTRICITY MONTHLY', 'Meter', '', '%');

/* input_data: 2d Array   eg.  array['INTERIORLIGHTS:ELECTRICITY']
 * start_month: integer eg. 1 = January
 * end_month: integer eg. 3 = March
 * return a string eg. "1 , 2, 3, 4"
 */
function getDataByMonth($input_data, $start_month, $end_month) {
	
	$index = 0;
	$counter = 1;       			// Start From January
	$output = "";

	if($start_month < 1  | $end_month > 12) {
		return 1;
	}

	foreach ($input_data as $input)
	{

		if($counter >= $start_month && $counter <= $end_month) {
			if($input > 0) 
				$output[$index] = $input;
			else
				$output[$index] = 0;
			//echo "###";
			$index = $index + 1;
		}
		$counter = $counter + 1;
	}

	return $output;
}

//print_r(getDataByMonth($eem1_e_vals['INTERIORLIGHTS:ELECTRICITY'], 4, 6));
	
//print_r(getDataByMonth($base2_e_vals['INTERIORLIGHTS:ELECTRICITY'], 1, 3));
/*
print_r(getDataByMonth($eem1_e_vals['INTERIORLIGHTS:ELECTRICITY'], 4, 6));
print_r(getDataByMonth($eem2_e_vals['INTERIORLIGHTS:ELECTRICITY'], 7, 10));
print_r(getDataByMonth($eem3_e_vals['INTERIORLIGHTS:ELECTRICITY'], 11, 12));
*/

# new_val is array of number
# cur_val is array of number
# add new_val to cur_val and return cur_val
function cumulateValues($cur_val, $new_val) {
	$i = 0;
	$val;
	foreach($new_val as $v) {
		$val[$i] = $v;
		$i++;
	}
	
	return $val;
}

function printTableRowData($baseline, $eem1, $eem2, $eem3) {
	foreach($baseline as $bl)
		echo "<td title='baseline value' >".number_format($bl)."</td>";
	foreach($eem1 as $e1)
		echo "<td title='eem1 value'>".number_format($e1)."</td>";
	foreach($eem2 as $e2)
		echo "<td title='eem2 value'>".number_format($e2)."</td>";
	foreach($eem3 as $e3)
		echo "<td title='eem3 value'>".number_format($e3)."</td>";
}

/* merge 4 arrays of number to a string
 * base: array of number
 * eem1: array of number
 * eem2: array of number
 * eem3: array of number
 * return a string of data eg. ["1, 2, 3, 4, 5, 6 ..., 12"]
 */
function convertToDataString($base, $eem1, $eem2, $eem3) {

	$dataString = "";	

	foreach($base as $b) 
		$dataString = "$dataString $b,";
	foreach($eem1 as $e1) 
			$dataString = "$dataString $e1,";
	foreach($eem2 as $e2) 
			$dataString = "$dataString $e2,";
	foreach($eem3 as $e3) 
			$dataString = "$dataString $e3,";
	
	return $dataString;
}

// EEM Pump Data
$base_pump = getDataByMonth($base2_e_vals['PUMPS:ELECTRICITY'], 1, 3);
$eem1_pump = getDataByMonth($eem1_e_vals['PUMPS:ELECTRICITY'], 4, 6);
$eem2_pump = getDataByMonth($eem2_e_vals['PUMPS:ELECTRICITY'], 7, 10);
$eem3_pump = getDataByMonth($eem3_e_vals['PUMPS:ELECTRICITY'], 11, 12);

$pump_data = "[".convertToDataString($base_pump, $eem1_pump, $eem2_pump, $eem3_pump)."]";
#print_r($base_pump);

// EEM Fan Data
$base_fan = cumulateValues($base_pump, getDataByMonth($base2_e_vals['FANS:ELECTRICITY'], 1, 3));
$eem1_fan = cumulateValues($eem1_pump, getDataByMonth($eem1_e_vals['FANS:ELECTRICITY'], 4, 6));
$eem2_fan = cumulateValues($eem2_pump, getDataByMonth($eem2_e_vals['FANS:ELECTRICITY'], 7, 10));
$eem3_fan = cumulateValues($eem3_pump, getDataByMonth($eem3_e_vals['FANS:ELECTRICITY'], 11, 12));

$fan_data = "[".convertToDataString($base_fan, $eem1_fan, $eem2_fan, $eem3_fan)."]";
#print_r($base_fan);

// EEM Equiment Data
$base_eqmt = cumulateValues($base_fan, getDataByMonth($base2_e_vals['INTERIOREQUIPMENT:ELECTRICITY'], 1, 3));
$eem1_eqmt = cumulateValues($eem1_fan, getDataByMonth($eem1_e_vals['INTERIOREQUIPMENT:ELECTRICITY'], 4, 6));
$eem2_eqmt = cumulateValues($eem2_fan, getDataByMonth($eem2_e_vals['INTERIOREQUIPMENT:ELECTRICITY'], 7, 10));
$eem3_eqmt = cumulateValues($eem3_fan, getDataByMonth($eem3_e_vals['INTERIOREQUIPMENT:ELECTRICITY'], 11, 12));

$equipment_data = "[".convertToDataString($base_eqmt, $eem1_eqmt, $eem2_eqmt, $eem3_eqmt)."]";
#print_r($base_eqmt);

// EEM Lighting Data
$base_light = cumulateValues($base_eqmt, getDataByMonth($base2_e_vals['INTERIORLIGHTS:ELECTRICITY'], 1, 3));
$eem1_light = cumulateValues($eem1_eqmt, getDataByMonth($eem1_e_vals['INTERIORLIGHTS:ELECTRICITY'], 4, 6));
$eem2_light = cumulateValues($eem2_eqmt, getDataByMonth($eem2_e_vals['INTERIORLIGHTS:ELECTRICITY'], 7, 10));
$eem3_light = cumulateValues($eem3_eqmt, getDataByMonth($eem3_e_vals['INTERIORLIGHTS:ELECTRICITY'], 11, 12));

$lighting_data = "[".convertToDataString($base_light, $eem1_light, $eem2_light, $eem3_light)."]";
#print_r($eem2_light);

// EEM Cooling Data
$base_cool = cumulateValues($base_light, getDataByMonth($base2_e_vals['COOLING:ELECTRICITY'], 1, 3));
$eem1_cool = cumulateValues($eem1_light, getDataByMonth($eem1_e_vals['COOLING:ELECTRICITY'], 4, 6));
$eem2_cool = cumulateValues($eem2_light, getDataByMonth($eem2_e_vals['COOLING:ELECTRICITY'], 7, 10));
$eem3_cool = cumulateValues($eem3_light, getDataByMonth($eem3_e_vals['COOLING:ELECTRICITY'], 11, 12));

$cooling_data = "[".convertToDataString($base_cool, $eem1_cool, $eem2_cool, $eem3_cool)."]";
#print_r($base_cool);

// EEM Heating Data
$base_heat = cumulateValues($base_cool, getDataByMonth($base2_e_vals['HEATING:ELECTRICITY'], 1, 3));
$eem1_heat = cumulateValues($eem1_cool, getDataByMonth($eem1_e_vals['HEATING:ELECTRICITY'], 4, 6));
$eem2_heat = cumulateValues($eem2_cool, getDataByMonth($eem2_e_vals['HEATING:ELECTRICITY'], 7, 10));
$eem3_heat = cumulateValues($eem3_cool, getDataByMonth($eem3_e_vals['HEATING:ELECTRICITY'], 11, 12));

$heating_data = "[".convertToDataString($base_heat, $eem1_heat, $eem2_heat, $eem3_heat)."]";
#print_r($base_heat);
#print_r($base_e_vals);
#printMonthlyData($base_e_vals["January"]);

/*
 * convert an array of number to a string 
 * $row is an array of positive number
 */
function printRow($row){
	foreach($row as $v) {
		if($v >=0) {
			 "<td> $v </td>";
		} else {
			 "<td> 0.0 </td>";
		}
	}
}

/*
 * convert an array of positive number to string "1, 2, 3, 4, ..."
 * $row is an array of positive number 
 */
function printMonthlyData($row){
	 '[';
	foreach($row as $v) {
		if($v > 0)
			 "$v, ";
		else
			 "0.0, ";
	}
	 ']';
}

#printMonthlyData($e_vals['INTERIOREQUIPMENT:ELECTRICITY']);
#printRow($e_vals['INTERIOREQUIPMENT:ELECTRICITY']);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>EEB Hub Simulation Tools: Comprehensive</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-responsive.css" rel="stylesheet">
    <link href="css/docs.css" rel="stylesheet">
    <link href="css/comprehensive.css" rel="stylesheet">

    <style>
      body {
        /*background: linear-gradient(to bottom, #999, #fff); */
        padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
      }
      .container{
        width: 90%;
      }
      .table-striped{
        background: #ccc;
      }
      .table{
        text-align: center;
      }
    </style>

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="../assets/js/html5shiv.js"></script>
    <![endif]-->
  </head>

  <body>
    <!-- Navbar
    ================================================== -->
    <? $ui->drawNavbar();?>

    <!-- Container -->
    <div class="container">

		<!-- Switch EEMs for comparison-->
		<form action="" method="post">
      Baseline
			<select name="Model[0]" onchange="this.form.submit();">
	      <?php
          echo "<option value=\"{$_POST[Model][0]}\">{$_POST[Model][0]}</option>";
          foreach( $_SESSION['Model'] as $eem_model ) {
            echo "<option value=\"$eem_model\">$eem_model</option>";
          }       
        ?>
			</select>
      Mar - Jul
      <select name="Model[1]" onchange="this.form.submit();">
        <?php
          echo "<option value=\"{$_POST[Model][1]}\">{$_POST[Model][1]}</option>";
          foreach( $_SESSION['Model'] as $eem_model ) {
            echo "<option value=\"$eem_model\">$eem_model</option>";
          }       
        ?>
      </select>
      Jul - Oct
      <select name="Model[2]" onchange="this.form.submit();">
        <?php
          echo "<option value=\"{$_POST[Model][2]}\">{$_POST[Model][2]}</option>";
          foreach( $_SESSION['Model'] as $eem_model ) {
            echo "<option value=\"$eem_model\">$eem_model</option>";
          }       
        ?>
      </select>
      Oct - Dec
      <select name="Model[3]" onchange="this.form.submit();">
        <?php
          echo "<option value=\"{$_POST[Model][3]}\">{$_POST[Model][3]}</option>";
          foreach( $_SESSION['Model'] as $eem_model ) {
            echo "<option value=\"$eem_model\">$eem_model</option>";
          }       
        ?>
      </select>
		</form>


        <!-- Sub-Nav-bar -->
        <? $page[comparison]="active"; $ui->drawSubNavbar($page); ?>

      <!-- Tab Content -->
      
        <div id="site-energy-chart" style="min-width: 310px; height: 500px; margin: 0 auto"></div>

        <!-- Electricity Consumption Table -->
        <table class="table table-striped table-bordered" style="margin: 40px auto; width: 100%">
          <caption style="background: linear-gradient(to right, green, purple,yellow); color: #fff;"> <h3>Electricity Energy Consumption (kWh)<h3> </caption>
          <tr id="table-row-head">
            <th> -
            </th>
			      <th> Jan
            </th>
            <th> Feb
            </th>
            <th> Mar
            </th>
            <th> Apr
            </th>
			      <th> May
			      </th>
            <th> Jun
            </th>
            <th> Jul
            </th>
            <th> Aug
            </th>
            <th> Sep
            </th>
			      <th> Oct
            </th>
            <th> Nov
            </th>
            <th> Dec
            </th>
          </tr>
          <tr class="table-row-even">
            <th> Heating
            </th>
			      <?php printTableRowData($base_heat, $eem1_heat, $eem2_heat, $eem3_heat); ?>
          </tr>
          <tr class="table-row-odd">
            <th> Cooling
            </th>
			      <?php printTableRowData($base_cool, $eem1_cool, $eem2_cool, $eem3_cool); ?>
          </tr>
          <tr class="table-row-even">
            <th> Interior Lighting
            </th>
			      <?php printTableRowData($base_light, $eem1_light, $eem2_light, $eem3_light); ?>
          </tr>
          <tr class="table-row-odd">
            <th> Interior Equipment
            </th>
			      <?php printTableRowData($base_eqmt, $eem1_eqmt, $eem2_eqmt, $eem3_eqmt); ?>
          </tr>
          <tr class="table-row-even">
            <th> Fans
            </th>
			      <?php printTableRowData($base_fan, $eem1_fan, $eem2_fan, $eem3_fan); ?>
          </tr>
          <tr class="table-row-odd">
            <th> Pumps
            </th>
			      <?php printTableRowData($base_pump, $eem1_pump, $eem2_pump, $eem3_pump); ?>
          </tr>
        </table>
    </div> <!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- load highchart libs -->
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <script src="js/Highcharts-3.0.4/js/highcharts.js"></script>
    <script src="js/Highcharts-3.0.4/js/modules/exporting.js"></script>
     <!-- Placed at the end of the document so the pages load faster -->
    <script type="text/javascript" src="js/bootstrap.min.js"></script>

    <!-- Charts' Defination -->
    <script>
      $(function () {
          $('#site-energy-chart').highcharts({
              chart: {
                  zoomtype: 'xy'
              },
              title: {
                  text: ''
              },
              legend: {
                  layout: 'horizontal',
                  align: 'right',
                  verticalAlign: 'top',
                  x: 0,
                  y: 0,
                  floating: true,
                  borderWidth: 1,
                  fontSize: '18px',
                  backgroundColor: '#FFFFFF'
              },
              xAxis: {
                  categories: [
                      'Jan',
                      'Feb',
                      'Mar',
                      'Apr',
                      'May',
                      'Jun',
                      'July',
                      'Aug',
                      'Sep',
                      'Oct',
                      'Nov',
                      'Dec'
                  ],
                  plotBands: [{ // visualize the weekend
                      from: 0,
                      to: 0,
                      color: 'rgba(68, 170, 213, .2)'
                  }]
              },
              yAxis: {
                  title: {
                      text: 'Electricity Comsumption (kWh)'
                  }
              },
              tooltip: {
                  shared: true,
                  valueSuffix: ' kWh'
              },
              credits: {
                  enabled: false
              },
              plotOptions: {
                  line: {
                      fillOpacity: 1
                  },
  				    area: {
                      stacking: 'normal',
                      lineColor: '#666666',
                      lineWidth: 1,
                      marker: {
                          lineWidth: 1,
                          lineColor: '#666666'
                      }
                  }
              },
              series: [{
                  name: 'Baseline',
                  type: 'line',
                  data: <?php echo $baseline_data;?>
              }, {
                  name: 'Heating',
                   type: 'area',
  				    color: "red",
                  data: <?php echo $heating_data;?>
              }, {
                  name: 'Cooling',
                   type: 'area',
                  data: <?php echo $cooling_data;?>
              },{
                  name: 'Interior Lighting',
                  type: 'area',
                  data: <?php echo $lighting_data;?>
              }, {
                  name: 'Equipment',
                  type: 'area',
                  data: <?php echo $equipment_data;?>
              }, {
                  name: 'Fans',
                  type: 'area',
                  data: <?php echo $fan_data;?>
              }, {
                  name: 'Pumps',
                  type: 'area',
                  data: <?php echo $pump_data;?>
              }]
          });
      });
    </script>

  </body>
</html>
