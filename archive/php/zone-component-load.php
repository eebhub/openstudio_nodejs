<?php
	require 'php/EEB_SQLITE3.php';
	require 'php/EEB_UI.php';

	session_start();
	$ui = new EEB_UI;   // default user interface

	// define the sql file path
	if ($_POST['num_package'] != NULL) {
		$cur_model = $_SESSION['cur_model'] = $_POST['num_package'];
	} elseif($_POST['num_package'] == NULL && $_SESSION['cur_model'] == NULL) {
		$cur_model = $_SESSION[cur_model];
	} else {
		$cur_model = $_SESSION[cur_model];
	}

	// baseline sql file path
    if($cur_model == $_SESSION['Model'][0]) {
       $sql_file="ENERGYPLUS/idf/{$cur_model}/EnergyPlusPreProcess/EnergyPlus-0/eplusout.sql";
    } else { // eem sql file path
     $sql_file="eem/$_SESSION[user_dir]/Output/{$cur_model}.sql";
    }

	$eeb = new EEB_SQLITE3("$sql_file");

	if( $_GET['for'] == '' && $_GET['table'] == ''){
		$_SESSION['for'] = '1ST FLOOR-MIDDLE';
		$_SESSION['table'] = 'Estimated Cooling Peak Load Components';
	}
	else {
		if($_GET['for'] == '') {} 
		else { $_SESSION['for'] = $_GET['for'];}

		if($_GET['table'] == '') {} 
		else {$_SESSION['table'] = $_GET['table'];}
	}

	$load_vals = $eeb->getValuesByColumn('ZoneComponentLoadSummary', $_SESSION['for'], $_SESSION['table'], '%');
	$zone = $eeb->getReportForStrings('ZoneComponentLoadSummary');

	function printZone($zone){
		foreach($zone as $z) {
			if($z == $_SESSION['for']) {
				echo "<li class='selected'> <a href='?for=$z'> $z </a></li>";
			}
			else {
				echo "<li> <a href='?for=$z'> $z </a></li>";
			}
		}
	}

	function getNetSensibleLoad($total, $latent) {
		$total_load = NULL;	
		$net_load = NULL;
		$i = 0;

		foreach($total as $t) {
			$total[] = $t;
		}

		foreach($latent as $l) {
			$net_load[] = $total[$i++] - $l;
		}

		return $net_load;
	}

	function printLoadData($load_vals) {
		echo '[';
		foreach($load_vals as $c) {
			echo "$c, ";
		}
		echo ']';
	}
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
	<link href="css/bootstrap.css" rel="stylesheet">
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/bootstrap-responsive.css" rel="stylesheet">
	<link href="css/docs.css" rel="stylesheet">
	<link href="css/comprehensive.css" rel="stylesheet">    

	<style>
		.selected {
			background: yellow;
		}
	</style>
	</head>

	<body>
    <!-- Navbar 
    ================================================== -->
    <? $ui->drawNavbar();?>

    <!-- Container -->
    <div class="container">

    <!-- Switch Pacakge -->
    <? $ui->drawSwitchPackage();?>

        <!-- Sub-Nav-bar -->
        <? $page[zone]="active"; $ui->drawSubNavbar($page); ?>
 		

        	<!-- Zone Components Load Summary-->
            <div class="container-fluid" style="background: white;">
                <div class="row-fluid">
                    <!--Sidebar content-->
                    <div class="span3" >
                    	<legend>Thermal Zones </legend>
                      	<div style="height: 600px; overflow: scroll; overflow-x: hidden; padding-top: 0px; scroller"> 
		                  <!-- Link or button to toggle dropdown -->
		                  <ul class="unstyled" >
		                    <?php printZone($zone); ?>
		                  </ul>
						</div>
                   	</div>

                    <!--Body content-->
                    <div class="span9">
                    
                    <!-- Cooling or Heating -->
                    <ul class="nav nav-tabs">
                        <li >
                        <a href="#">Annual</a>
                        </li>
						<?php
							echo '<li';
							if($_SESSION['table'] == 'Estimated Heating Peak Load Components'){ echo ' class="active"';}
							echo '><a href="?table=Estimated Heating Peak Load Components">Heating Design Day</a></li>';

							echo '<li';
		                    if($_SESSION['table'] == 'Estimated Cooling Peak Load Components'){ echo ' class="active"';}
							echo '><a href="?table=Estimated Cooling Peak Load Components">Cooling Design Day</a></li>';
						?>
                    </ul>
                        
                    <div id="zone_cooling_chart" style="min-width: 310px; width: 100%; min-height: 600px; margin: 0 auto"></div>
                    </div>
                </div>
            </div>

        
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

    <!-- Highcharts -->
    <script>
     $(function () {
        $('#zone_cooling_chart').highcharts({
            chart: {
                type: 'bar',
                margin: [ 50, 50, 50, 200]
            },
            title: {
                text: 'Zone Component Load Summary Without Latent (Btu/h) '
            },
            xAxis: {
                categories: [
                    'People:',
                    'Lights:',
                    'Equipment:',
                    'Refrigeration:',
                    'Water Use Equipment:',
                    'HVAC Equipment Losses:',
                    'Power Generation Equipment:',
                    'Infiltration:',
                    'Zone Ventilation',
                    'Interzone Mixing:',
                    'Roof:',
					'Interzone Ceiling',
                    'Other Roof:',
                    'Exterior Wall:',
                    'Interzone Wall:',
					'Ground Contact Wall:',
                    'Other Wall:',
                    'Exterior Floor:',
                    'Interzone Floor:',
                    'Ground Contact Floor:',
                    'Other Floor:',
                    'Fenestration COnduction:',
					'Fenestration Solar:',
					'Opaque Door:',
					'Net Sensible Load:'
                ],
                labels: {
                    rotation: 0,
                    align: 'right',
                    style: {
						width: '200px',
                        fontSize: '12px',
                        fontFamily: 'Verdana, sans-serif'
                    }
                }
            },
            yAxis: {
                min: <?php echo min($load_vals['Total']);?>,
                title: {
                    text: ''
                }
            },
            legend: {
                enabled: false
            },
            tooltip: {
                pointFormat: 'Load / hour Summary: <b>{point.y:.1f} Btu/h</b>',
            },
            series: [{
                name: 'Energy Per Hour',
                data: <?php printLoadData(getNetSensibleLoad($load_vals['Total'], $load_vals['Latent']));?>,
                dataLabels: {
                    enabled: true,
                    rotation: 0,
                    color: 'red',
                    align: 'left',
                    x: 10,
                    y: 8,
                    style: {
                        fontSize: '12px',
                        fontFamily: 'Verdana, sans-serif',
                        textShadow: '0 0 3px #eee'
                    }
                }
            }]
        });
    });
    </script>
  </body>
</html>
