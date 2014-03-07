<?php
require 'php/EEB_SQLITE3.php';



session_start();

//echo $_SESSION[cur_model];
// initialize the game variables
if($_SESSION['current_yr'] == '') {
  $_SESSION['current_yr'] = 0;
  $_SESSION['installationCost'][0] = 0;
  
  // add a new energy simulation cost
  $sql_file="ENERGYPLUS/idf/$_SESSION[cur_model]/EnergyPlusPreProcess/EnergyPlus-0/eplusout.sql";
  $eeb = new EEB_SQLITE3("$sql_file");
  $electric_tariff = $eeb->getValues('Tariff Report', 'BLDG101_ELECTRIC_RATE', 'Categories', '%');
  $gas_tariff = $eeb->getValues('Tariff Report', 'BLDG101_GAS_RATE',  'Categories', '%');
  $newCost = round($electric_tariff['Total (~~$~~)'][Sum] + $gas_tariff['Total (~~$~~)'][Sum],0);

  $_SESSION['newCost'][0] = $newCost;
  $_SESSION['availableCap'][0] = 0;  
  $_SESSION['availableCap'][1] = 100000;
  $_SESSION['availableCap2'][1] = 100000;
  $_SESSION['remainingCap'][0] = 0;
  $_SESSION['cumulatedSaving'][0] = 0;  
  $_SESSION['3PercentInterestRate'][0] = 0;
  $_SESSION['remainingCapPlusCumulatedSaving'][0] = 0;
  $_SESSION['percentageSaving'][0]=0;
}

// create a user folder that is based on the client ip address and the timestamp
if($_SESSION['user_dir'] == '') {
  // the name of the user dir
  $user_dir = $_SESSION['user_dir'] = $_SESSION[building_name]."D";//.md5($_SERVER['REMOTE_ADDR'].time());
  mkdir("eem/$user_dir", 0775);
  // save fold :  /eem/$user_dir/
}

$current_yr = &$_SESSION['current_yr'];
$eem_version = &$_SESSION['eem_cnt'];

/*
 *  the function resets the finishedMeasures from the current year to the restarted year
 *  the function has side effect to the sessions variable such that
 *      current_yr is set to restart_yr, both yrs are greater than 0
 *      im is the installedMeasures list that will be reset
 *      fm is the finishedMeasures list that will be reset
 *      eem_cnt is the counter for the file of eem version, will be set to current_yr
 */
function resetMeasureYear($restart_yr, &$current_yr, &$im, &$fm) {

   $im = &$_SESSION['installed_measures'];   // installed measures list in each year (the index is int) 
   $fm = &$_SESSION['measureFinished'];      // finished measures list shows the available of measures

   if($restart_yr < 0 || $current_yr < 0) {
      echo "cant reset the year < 0";
      return 1;
   } else {
      // undo finishedMeasures from the fm list, so user can re-select the finishedMeasures 
      for($y = $current_yr-1;  $y > $restart_yr-1; $y--) {
         foreach($im[$y] as &$m) {
            $fm[$m] ='';
         }
  
         // remove eem model from the session model
         unset($_SESSION['Model'][$y+1]);    
     
         // delete eem_#.idf file-dir    
     removeEEM($y+1);
     $yy = $y+1;
     `rm eem/{$_SESSION['user_dir']}/eem_{$yy}.idf`;
      }
      
      // reset the current year to restart_yr
      $current_yr = $restart_yr; 
      $_SESSION['eem_cnt'] = $current_yr;
    $_SESSION['cur_model'] = $_SESSION['Model'][$current_yr];
   }
}

/*
 *  remove eem files
 */
function removeEEM($eem_version) {
   $eem_idf = "eem/{$_SESSION['user_dir']}/eem_{$eem_version}.idf";
   $files_result = "eem/{$_SESSION['user_dir']}/Output/eem_{$eem_version}*";
   echo `rm -r $files_result`;
}

// Do resetMeasureYear
if(isset($_POST['reset'])) resetMeasureYear($_POST['reset'], $current_yr, $_SESSION['installed_measures'], $_SESSION['measureFinished']);

function selectedList($current_yr, $year, $finieshedMeasure, $measures, $measure_i) {
$MeasureIndex =array("none"=>"none","bmsSBChecked"=>"Building Management System","energyStarEquipmentChecked"=>"EnergyStar Equipment","plugLoadChecked"=>"Plug Load Control", "oblsChecked"=>"Occupancy-Based Lighting Sensors","daylightDimmingChecked"=>"Daylight-Based Dimming","OfficefixturedChecked"=> "Office Lighting Fixture Upgrade", "bathroomFixturedChecked"=>"Bathroom Lighting Fixture Upgrade","emergencyLightingChecked"=>"Emergency Lighting Upgrade", "roofInsulationChecked" => "Increase Roof Insulation by R-10", "wallInsulation1Checked" => "Increase Wall Insulation by R-10", "wallInsulation2Checked" => "Increase Wall Insulation by R-20", "windowsUpgradelChecked" => "Window Upgrade", "windowsFilmChecked" => "Window Film", "doorWeatherizationChecked" => "Door Weatherization", "enclosureRecommisChecked" => "Exterior Wall Weatherization*", "airEconChecked" => "Outdoor Air Economizer", "condensingBoilerChecked" => "Condensing Boiler", "sysEffChecked" => "Condensing Unit Replacement");
    
if($current_yr > $year) {
        echo "<b>".$MeasureIndex[$finieshedMeasure]."</b>";
    }
    else if($current_yr == $year) {
        echo "<select name='selected[]' id='install_".$current_yr."_".$measure_i."' onchange='installCostValidate(this, ".$current_yr.");'>
            <option value='none'>-none-</option>";
            if($measures['bmsSBChecked'] != 'finished') echo "<option value='bmsSBChecked'>Building Management System ($50,000)</option>";
            if($measures['energyStarEquipmentChecked'] != 'finished') echo "<option value='energyStarEquipmentChecked'>EnergyStar Equipment ($15,000)</option>";
            if($measures['plugLoadChecked'] != 'finished') echo "<option value='plugLoadChecked'>Plug Load Control ($60,000)</option>";
            //if($measures['oblsChecked'] != 'finished') echo "<option value='oblsChecked'>Occupancy-Based Lighting Sensors ($50,000)</option>";
            if($measures['daylightDimmingChecked'] != 'finished') echo "<option value='daylightDimmingChecked'>Daylight-Based Dimming ($75,000)</option>";
            if($measures['OfficefixturedChecked'] != 'finished') echo "<option value='OfficefixturedChecked'>Office Lighting Fixture Upgrade ($200,000)</option>";
            //if($measures['bathroomFixturedChecked'] != 'finished') echo "<option value='bathroomFixturedChecked'>Bathroom Lighting Fixture Upgrade ($8,000)</option>";
            //if($measures['emergencyLightingChecked'] != 'finished') echo "<option value='emergencyLightingChecked'>Emergency Lighting Upgrade ($40,000)</option>";
            if($measures['roofInsulationChecked'] != 'finished') echo "<option value='roofInsulationChecked'>Increase Roof Insulation by R-10 ($50,000)</option>";
            if($measures['wallInsulation1Checked'] != 'finished') echo "<option value='wallInsulation1Checked'>Increase Wall Insulation by R-10 ($100,000)</option>";
            if($measures['wallInsulation2Checked'] != 'finished') echo "<option value='wallInsulation2Checked'>Increase Wall Insulation by R-20 ($130,000)</option>";
            if($measures['windowsUpgradelChecked'] != 'finished') echo "<option value='windowsUpgradelChecked'>Window Upgrade ($100,000)</option>";
            if($measures['windowsFilmChecked'] != 'finished') echo "<option value='windowsFilmChecked'>Window Film ($25,000)</option>";
            if($measures['doorWeatherizationChecked'] != 'finished') echo "<option value='doorWeatherizationChecked'>Door Weatherization ($5,000)</option>";
            if($measures['enclosureRecommisChecked'] != 'finished') echo "<option value='enclosureRecommisChecked'>Exterior Wall Weatherization* ($25,000)</option>";
            //if($measures['airEconChecked'] != 'finished') echo "<option value='airEconChecked' disabled>Outdoor Air Economizer ($26,620 at most)</option>";
            if($measures['condensingBoilerChecked'] != 'finished') echo "<option value='condensingBoilerChecked'>Condensing Boiler ($51,225 at most)</option>";
            if($measures['sysEffChecked'] != 'finished') echo "<option value='sysEffChecked'>Condensing Unit Replacement ($210,000 at most)</option>";
        echo "</select>";
    } else {
        echo "";
    }
} // End of function selectedList()
?>

<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="utf-8">
    <title>EEB Hub Simulation Platform</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="eebhub">

    <!-- Le styles -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-responsive.css" rel="stylesheet">
    <link href="css/docs.css" rel="stylesheet">
    <link href="css/comprehensive.css" rel="stylesheet">

    <style>
      body {
        width: 2300px;
        
      }
      .container{
        width: 2200px;
      }
      .table-striped{
        background: #eee;
      }
      .table{
        text-align: center;
        box-shadow: 0px 3px 5px #999;
      }
      .table-striped th {
         color: #333;
         font-size: 16px;
         text-align: center;
      }
      .table-striped td {
        text-align: center;
        font-size: 14px;
      }
      b {
        color: green;
      }
      table button {
        width: 100%;
        color: red;
      }
      #restart-button{
        background: #295;
        color:white;
        position: absolute;
        left: 50px;
        width: 115px;
        opacity: 0;
      }
      button:disabled{
        color: #999;
      }
      #restart-button:hover {
        opacity: 1;
      }
      #restart-game-button{
        padding: 8px;
        font-size: 20px;
        background: #669933;
        color: white;
        margin-bottom: 5px; 
      }
      #restart-game-button:hover {
         background: #666633;
      }
      th[data-title]:hover{
        background-color: yellow;
        position: relative;
      }

      th[data-title]:hover:after {
        content: attr(data-title);
        padding: 4px 8px;
        color: #333;
        position: absolute;
        left: 0;
        top: 100%;
        white-space: nowrap;
        z-index: 20px;
        -moz-border-radius: 5px;
        -webkit-border-radius: 5px;
        border-radius: 5px;
        -moz-box-shadow: 0px 0px 4px #222;
        -webkit-box-shadow: 0px 0px 4px #222;
        box-shadow: 0px 0px 4px #222;
        background-image: -moz-linear-gradient(top, #eeeeee, #cccccc);
        background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0, #eeeeee),color-stop(1, #cccccc));
        background-image: -webkit-linear-gradient(top, #eeeeee, #cccccc);
        background-image: -moz-linear-gradient(top, #eeeeee, #cccccc);
        background-image: -ms-linear-gradient(top, #eeeeee, #cccccc);
        background-image: -o-linear-gradient(top, #eeeeee, #cccccc);
      }
     .label-name{
       color:rgb(135, 174, 154);
      }
    </style>

  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
  <script src="http://code.jquery.com/jquery-1.7.js"></script>
  <script>
  $(document).ready(function(){
  $("#install_cost1").focusout(function() {
    //alert($("#install_cost1").val());
  });
  });
  </script>
  <script>
  function install_cost1_cal(){
    var temp=parseInt($("#install1_1").val())+ parseInt($("#install1_2").val())+parseInt($("#install1_3").val())+parseInt($("#install1_4").val())+parseInt($("#install1_5").val());
    temp=temp.toString(); 
    var temp1 = temp.substring(0, temp.length-3);var temp2 = temp.substring(temp.length-3, temp.length);
    if((temp.length-3)>0){$("#install_cost1").html("$ "+temp1 +","+temp2);}else{$("#install_cost1").html("$ "+temp2);}
  }
  
  </script>

  <script type="text/javascript">
  function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  }

   var installation_costs = {none: 0, bmsSBChecked: 50000, energyStarEquipmentChecked: 15000,plugLoadChecked: 60000, oblsChecked: 50000,daylightDimmingChecked: 75000,OfficefixturedChecked: 200000,bathroomFixturedChecked:8000,emergencyLightingChecked:40000,roofInsulationChecked:50000,wallInsulation1Checked:100000,wallInsulation2Checked: 130000,windowsUpgradelChecked:100000,windowsFilmChecked:25000,doorWeatherizationChecked:5000,enclosureRecommisChecked:25000,airEconChecked:26620,condensingBoilerChecked:51225,sysEffChecked:210000};

  function installCostValidate(this_measure, year){
    var install_cost1 = installation_costs[$("#install_"+year+"_0").val()];
    var install_cost2 = installation_costs[$("#install_"+year+"_1").val()];
    var install_cost3 = installation_costs[$("#install_"+year+"_2").val()];
    var install_cost4 = installation_costs[$("#install_"+year+"_3").val()];
    var install_cost5 = installation_costs[$("#install_"+year+"_4").val()];
    
    var total_cost = install_cost1 + install_cost2 + install_cost3 + install_cost4 + install_cost5;
    //alert(install_cost1+install_cost2+ install_cost3+ install_cost4+ install_cost5);

   available_cap = <?php if($_SESSION['current_yr']==''||$_SESSION['current_yr']==0) print($_SESSION['availableCap2'][1]); 
   else {$currentyear = $_SESSION['current_yr'];$temp = $_SESSION['availableCap2'][$currentyear]-$_SESSION['installationCost'][$currentyear]+$_SESSION['newCost'][0]-$_SESSION['newCost'][$currentyear]+ 100000; print($temp);} ?>;

     //alert(available_cap);
     available_cap = Math.ceil(available_cap);
     if (available_cap - total_cost<0){
       alert("You do not have enough money right now to install this measure.");
       //reset this measure option to be none
       $(this_measure).val('none');
     } else {
      $("#install_cost"+(year+1)).html("$ "+numberWithCommas(total_cost));
     }
  }
  </script>
  </head>

  <body>
    <!-- Navbar
    ================================================== -->
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid" >
      <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="brand pull-left" href="http://tools.eebhub.org">EEB Hub Simulation Platform</a>
        <div class="nav-collapse collapse">
            <ul class="nav">
                 <li>
                     <a href="http://tools.eebhub.org">LITE</a>
                 </li>
                 <li>
                     <a href="http://tools.eebhub.org">PARTIAL</a>
                 </li>
                 <li>
                     <a href="http://tools.eebhub.org">SUBSTANTIAL</a>
                 </li>
                 <li class="active">
                     <a href="http://tools.eebhub.org">COMPREHENSIVE</a>
                 </li>
             </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Container -->
    <div class="container">
     <h3>Tracking Sheet</h3>
        <!-- Sub-Nav-bar -->
        <div class="navbar">
          <div class="navbar-inner">
            <!--<a class="brand" href="#">EnergyPlus Output</a>-->
            <ul class="nav">
              <li class="active"><a href="tracking-sheet.php">Tracking Sheet</a></li>
              <li><a href="measure-list.php">Measures</a></li>
              <li><a href="./energy-use.php">Energy</a></li>
              <li><a href="./energy-cost.php">Energy Costs</a></li>
              <li><a href="./zone-component-load.php">Zone Loads</a></li>
              <li><a href="./energy-intensity.php">Energy Intensity</a></li>
            </ul>
          </div>
        </div>

        <h4>$<?php if($_SESSION['current_yr']==''||$_SESSION['current_yr']==0) print(number_format($_SESSION['availableCap2'][1])); 
    else {$currentyear = $_SESSION['current_yr'];$temp = $_SESSION['availableCap2'][$currentyear]-$_SESSION['installationCost'][$currentyear]+$_SESSION['newCost'][0]-$_SESSION['newCost'][$currentyear]+ 100000;echo number_format($temp);} ?>
      <span style="color: rgb(45, 149, 143);"> Current Available Capital  |  </span> 
      <? echo number_format($_SESSION['newCost'][0]);?> <span style="color: rgb(45, 149, 143);"> Baseline Annual Energy Costs</span> </h4>

<form>
<table border='1' class="table table-bordered table-striped">
  <tr>
    <th rowspan="2" data-title="The year when you will install the retrofit measure(s)" style="vertical-align:middle;">Year</th>
     <th colspan="3">ENERGY EFFICIENCY MEASURES</th>
     <th colspan="3">COSTS</th>
     <th colspan="2">SAVINGS</th>
     </tr>
   <tr>

  <th data-title="A list of available retrofit measures. You can install one or more at any year.">Install Measure #1</th>
  <th data-title="A list of available retrofit measures. You can install one or more at any year.">Install Measure #2</th>
  <th data-title="A list of available retrofit measures. You can install one or more at any year.">Install Measure #3</th>
  <th data-title="A list of available retrofit measures. You can install one or more at any year." style="display:none;">Installed 4</th>
  <th data-title="A list of available retrofit measures. You can install one or more at any year." style="display:none;">Installed 5</th>
  <th data-title="The total cost of the chosen measure(s) to be installed">Installation Cost*</th>
  <th data-title="Simulation button to determine the new annual energy use of the building after installing measure(s)">Simulation</th>
  <th data-title="New annual energy cost after installing all measures from the beginning of the game">New Annual Energy Cost</th>
<!--  <th data-title="Cumulative energy cost savings for simulation years">Cumulative Savings</th>
  <th data-title="The remaining capital plus the cumulative energy cost savings">$ Remaining Capital + Saving</th>
  <th data-title="Interest if invested at 3% real return">3% real interest rate comparison</th> -->
  <th data-title="%energy cost savings">% Energy Savings</th>
  <th>Pay Back (years)</th>
  </tr>

<?php
$percent_interest_rate = array(50000, 101500, 154545, 209181, 265457, 323420, 383123, 444617, 507955, 573194);

for($year = 2015; $year < 2025; $year++) {

$j = $year - 2014;

echo " <tr><th style='width: 100px;'>$year";
if($current_yr > $j-1){
  echo "<button id='restart-button' type='submit' name='reset' value='".($j-1)."' formaction='' formmethod='post'>Restart Here</button>";
}
echo "</th>";

for($i=0; $i < 5; $i++) {
  if (($i==3)||($i==4)){echo '<td style="display:none;">';}
   else echo '<td>';
    selectedList($current_yr, $j-1, $_SESSION['installed_measures'][$j-1][$i], $_SESSION['measureFinished'],$i);
    echo '</td>';
}

echo '
<td><span id="install_cost'.$j.'">$ ';

if($_SESSION['current_yr']==''||$_SESSION['current_yr']==0) {echo '-';}else if($j<=$_SESSION['current_yr']){
echo number_format($_SESSION['installationCost'][$j]);} else {echo '-';}
echo '</span></td>
<td><button onClick="load();" type="submit" formmethod="post" formaction="simulate-eem.php" ';
 
if($current_yr != $j-1) echo "disabled";

if($j == 1) {
  echo ">1st year</button></td>";
} else if ($j == 2) {
  echo ">2nd year</button></td>";
} else if ($j == 3) {
  echo ">3rd year</button></td>";
} else {
  $num = $j;  
  echo ">{$num}th year</button></td>";
}
  
echo "<td>
$ ";
if($_SESSION['current_yr']==''||$_SESSION['current_yr']==0) {echo number_format($_SESSION['newCost'][0]);}else if($j<=$_SESSION['current_yr']){
echo number_format($_SESSION['newCost'][$j]);} else {$currentyear = $_SESSION['current_yr'];echo number_format($_SESSION['newCost'][$currentyear]);}

echo "</td>";
// echo "<td>$ ";
// if($_SESSION['current_yr']==''||$_SESSION['current_yr']==0) {echo '0';}else if($j<=$_SESSION['current_yr']){
// echo number_format($_SESSION['cumulatedSaving'][$j]);}else {$currentyear = $_SESSION['current_yr'];echo number_format((132968-$_SESSION['newCost'][$currentyear])*($j-$currentyear)+$_SESSION['cumulatedSaving'][$currentyear]);}

// echo " </td>
// <td>";
// if ($j==10) echo "<strong>";
// echo "$ ";

// //add remainingCapPlusSaving and availableCap2 session if $j = $currentyear
// if($_SESSION['current_yr']==''||$_SESSION['current_yr']==0) {echo number_format($j*50000);}else if($j<$_SESSION['current_yr']){
// echo number_format($_SESSION['remainingCapPlusSaving'][$j]);} else if ($j==$_SESSION['current_yr']){$currentyear = $_SESSION['current_yr'];$_SESSION['remainingCapPlusSaving'][$currentyear]=$_SESSION['availableCap2'][$currentyear]-$_SESSION['installationCost'][$currentyear]+132968-$_SESSION['newCost'][$currentyear];$_SESSION['availableCap2'][$currentyear+1] = $_SESSION['remainingCapPlusSaving'][$currentyear]+50000;echo number_format($_SESSION['remainingCapPlusSaving'][$currentyear]);}
// else {$currentyear = $_SESSION['current_yr'];$temp = $_SESSION['availableCap2'][$currentyear]-$_SESSION['installationCost'][$currentyear]+($j+1-$currentyear)*(132968-$_SESSION['newCost'][$currentyear])+($j-$currentyear)*50000;echo number_format($temp);}

// if ($j==10) echo "</strong>";
// echo "</td>
// <td>";
// if ($j==10) echo "<strong>";
// echo "$ ".number_format($percent_interest_rate[$j-1]);
// if ($j==10) echo "</strong>";
// echo "</td>";
echo "<td>";
if($_SESSION['current_yr']==''||$_SESSION['current_yr']==0) {echo '-';}else if($j<=$_SESSION['current_yr']){
echo $_SESSION['percentageSaving'][$j];} else {$currentyear = $_SESSION['current_yr'];echo $_SESSION['percentageSaving'][$currentyear];}

echo " %</td><td>";
if($_SESSION['current_yr']==''||$_SESSION['current_yr']==0) {echo '-';}else if($j<$_SESSION['current_yr']){
echo number_format($_SESSION['payback'][$j]);}else if($j==$_SESSION['current_yr']){$temp = $_SESSION['installationCost'][$j]/($_SESSION['newCost'][$j-1]-$_SESSION['newCost'][$j]); $_SESSION['payback'][$j] = $temp; echo number_format($temp);} else {echo '-';}

echo "</td>
</tr>";
}  
?>


<tr>
<td colspan="9">
<strong>If the measure installation ran 5 more years...</strong>
</td>
</tr>
<tr>
<th>2025</th>
<td colspan="5" style="border: 0px"></td>
<td>$ 
<?php if($_SESSION['current_yr']==''||$_SESSION['current_yr']==0) {echo number_format($_SESSION['newCost'][0]);}else {$currentyear = $_SESSION['current_yr'];echo number_format($_SESSION['newCost'][$currentyear]);}
?>
</td>

<td>
<?php if($_SESSION['current_yr']==''||$_SESSION['current_yr']==0) {echo '-';}else {$currentyear = $_SESSION['current_yr'];echo $_SESSION['percentageSaving'][$currentyear];}
?>  
 %</td><td>-</td>
</tr>
<tr>
<th>2026</th>
<td colspan="5" style="border: 0px"></td>

<td>
$ 
<?php if($_SESSION['current_yr']==''||$_SESSION['current_yr']==0) {echo number_format($_SESSION['newCost'][0]);}else {$currentyear = $_SESSION['current_yr'];echo number_format($_SESSION['newCost'][$currentyear]);}
?>
</td>

<td><?php if($_SESSION['current_yr']==''||$_SESSION['current_yr']==0) {echo '-';}else {$currentyear = $_SESSION['current_yr'];echo $_SESSION['percentageSaving'][$currentyear];}
?>
 %</td><td>-</td>
</tr>
<tr>
<th>2027</th>
<td colspan="5" style="border: 0px"></td>

<td>
$ 
<?php if($_SESSION['current_yr']==''||$_SESSION['current_yr']==0) {echo number_format($_SESSION['newCost'][0]);}else {$currentyear = $_SESSION['current_yr'];echo number_format($_SESSION['newCost'][$currentyear]);}
?>
</td>

<td><?php if($_SESSION['current_yr']==''||$_SESSION['current_yr']==0) {echo '-';}else {$currentyear = $_SESSION['current_yr'];echo $_SESSION['percentageSaving'][$currentyear];}
?>
 %</td><td>-</td>
</tr>
<tr>
<th>2028</th>
<td colspan="5" style="border: 0px"></td>

<td>
$ 
<?php if($_SESSION['current_yr']==''||$_SESSION['current_yr']==0) {echo number_format($_SESSION['newCost'][0]);}else {$currentyear = $_SESSION['current_yr'];echo number_format($_SESSION['newCost'][$currentyear]);}
?>
</td>

<td><?php if($_SESSION['current_yr']==''||$_SESSION['current_yr']==0) {echo '-';}else {$currentyear = $_SESSION['current_yr'];echo $_SESSION['percentageSaving'][$currentyear];}
?>
 %</td><td>-</td>
</tr>
<tr>
<th>2029</th>
<td colspan="5" style="border: 0px"></td>

<td>
$ 
<?php if($_SESSION['current_yr']==''||$_SESSION['current_yr']==0) {echo number_format($_SESSION['newCost'][0]);}else {$currentyear = $_SESSION['current_yr'];echo number_format($_SESSION['newCost'][$currentyear]);}
?>
</td>

<td><?php if($_SESSION['current_yr']==''||$_SESSION['current_yr']==0) {echo '-';}else {$currentyear = $_SESSION['current_yr'];echo $_SESSION['percentageSaving'][$currentyear];}
?>
 %</td><td>-</td>
</tr>


</table>


<div class="pull-right">
<button type="submit" name="reset" value="0" formaction="" formmethod="post" id="restart-game-button" style="">RESTART</button>
   <!--a href="feedback.php" class="btn btn-large btn-success"> End Game</a-->
<!--button type="submit" value="go to feedback" formaction="http://rmt.eebhub.org/game/feedback.php" formmethod="post" id="restart-game-button" style="">END GAME</button-->
 </div> 
<br>

<p><i>* Installation Cost Disclaimer: Measure costs are not actual estimates, rather, they are generalized cost figures based on author judgments.</i></p>
 <br>

<h5 class="text-left">Built by the <a href="http://www.eebhub.org">Energy Efficient Buildings HUB</a>, a <a href="http://energy.gov/science-innovation/innovation/hubs">U.S. Department of Energy Innovation HUB</a>.  Powered by DOE's OpenStudio SDK & EnergyPlus Engine.  </h5>
 <h5 class="text-left">Our Retrofit Manager Tool team includes PSU, UMaryland, & NREL.  <a href="https://github.com/eebhub/platform/blob/master/ACKNOWLEDGEMENT_DISCLAIMER">DOE Acknowledgement & Disclaimer</a>.</h5>
 <br>

</form>
</div> <!-- /container -->
  
    <!-- loading bar -->
  <div id="loading-div" style="opacity: 0; box-shadow: 0 1px 5px #888; padding: 20px; text-align: center; width: 60%; background: #eee; z-index: -1; left: 20%; position: fixed; top: 50%;">
    <div class="progress" style="width: 100%;  height: 30px; background: #bbb;">
    <div id="loading-bar" style="background: linear-gradient(to right bottom, #3399ff, blue); height: 30px; width: 0px;"> </div>
    </div>
    <h4 id="loading-status">Simulation In Progress ...</h4>
  </div>
  </body>

    <script>  
  /* 
  *  the function shows the loading status
  */
  function load() {

    // top screen layer 
    var screen = document.createElement("div");
    screen.style.width = "100%";
    screen.style.height = "100%";
    screen.style.position = "absolute";
    screen.style.top="0";
    screen.style.background="#aaa";
    screen.style.opacity="0.5";
    screen.style.left="0";
    screen.style.zindex="5";
    document.body.appendChild(screen);

    $("#loading-div").css("z-index", "9999");
    $("#loading-div").css("opacity", "1");
    $("#loading-div").fadeIn();
    $('html,body').css('overflow', 'hidden');
    var progress = 0;
    var int=self.setInterval(function(){
    loading("loading-bar", Math.round(progress)+"%");
    progress += Math.random()*2; 
    if(progress >= 100) {
       loading("loading-bar", "100%");
       $("#loading-status").html("Loading ...");
       clearInterval(int);
    }
    },750);

   function loading(id, progress) {
   
     //console.log(id);
     //console.log(document.getElementById(id).style.width);
     var width = document.getElementById(id).style.width = progress;
     
     // update the loading-status (text)
     $("#loading-status").html("Simulation In Progress "+progress);
     //console.log(width);
   }
  } 

    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-26348074-7', 'eebhub.org');
    ga('send', 'pageview');
  </script>
</html>
