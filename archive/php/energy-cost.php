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

	$electric_tariff = $eeb->getValues('Tariff Report', 'BLDG101_ELECTRIC_RATE', 'Categories', '%');
	$gas_tariff = $eeb->getValues('Tariff Report', 'BLDG101_GAS_RATE',  'Categories', '%');

	function printRow($Row) {
		foreach ($Row as $r) {
			if($r > 0){
				echo "<td> $r </td>";
			} else {
				echo "<td> 0.0 </td>";
			}
		}
	}

	function getRowData($Row) {
		echo '[';
		foreach ($Row as $r) {
			if($r < $Row['Sum']){
					echo "$r,";
			}
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
        <? $page[cost]="active"; $ui->drawSubNavbar($page); ?>
 		
        <!-- Energy Intensity div -->
        <div>
          <div id="electricity_monthly_cost_chart" style="min-width: 400px; height: 400px; margin: 40px auto"></div>
          <!-- Electricity Consumption Table -->
          <table class="table table-striped table-bordered" style="margin: 40px auto; width: 100%">
            <caption style="background: purple; color: #fff;"> <h3>Electricity Monthly Cost<h3> </caption>
            <tr id="table-row-head">
              <th> 
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
              <th> Annual
              </th>
				<th> Max
              </th>
            </tr>
            <tr class="table-row-even">
              <th> Energy Charges ($)
              </th>
             <?php printRow($electric_tariff['EnergyCharges (~~$~~)']); ?>
            </tr>
            <tr class="table-row-odd">
              <th> Demand Charges ($)
              </th>
              <?php printRow($electric_tariff['DemandCharges (~~$~~)']); ?>
            </tr>
            <tr class="table-row-even">
              <th> Service Charge ($)
              </th>
              <?php printRow($electric_tariff['ServiceCharges (~~$~~)']); ?>
            </tr>
            <tr class="table-row-odd">
              <th> Adjustment ($)
              </th>
              <?php printRow($electric_tariff['Adjustment (~~$~~)']); ?>
            </tr>
            <tr class="table-row-odd">
              <th> Taxes ($)
              </th>
              <?php printRow($electric_tariff['Taxes (~~$~~)']); ?>
            </tr>
              <tr class="table-row-odd">
              <th> Total ($)
              </th>
              <?php printRow($electric_tariff['Total (~~$~~)']); ?>
            </tr>
           
          </table>

          <div id="natural_gas_monthly_cost_chart" style="min-width: 400px; height: 400px; margin: 40px auto"></div>
          <table class="table table-striped table-bordered" style="margin: 40px auto; width: 100%">
            <caption style="background: purple; color: #fff;"> <h3>Natural Gas Monthly Cost<h3> </caption>
            <tr id="table-row-head">
              <th> 
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
              <th> Annual
              </th>
				<th> Max
              </th>
            </tr>
            <tr class="table-row-even">
              <th> Energy Charges ($)
              </th>
             <?php printRow($gas_tariff['EnergyCharges (~~$~~)']); ?>
            </tr>
            <tr class="table-row-odd">
              <th> Demand Charges ($)
              </th>
              <?php printRow($gas_tariff['DemandCharges (~~$~~)']); ?>
            </tr>
            <tr class="table-row-even">
              <th> Service Charge ($)
              </th>
              <?php printRow($gas_tariff['ServiceCharges (~~$~~)']); ?>
            </tr>
            <tr class="table-row-odd">
              <th> Adjustment ($)
              </th>
              <?php printRow($gas_tariff['Adjustment (~~$~~)']); ?>
            </tr>
            <tr class="table-row-odd">
              <th> Taxes ($)
              </th>
              <?php printRow($gas_tariff['Taxes (~~$~~)']); ?>
            </tr>
              <tr class="table-row-odd">
              <th> Total ($)
              </th>
              <?php printRow($gas_tariff['Total (~~$~~)']); ?>
            </tr>
           
            </table>
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
          $('#electricity_monthly_cost_chart').highcharts({
              chart: {
                  zoomType: 'xy'
              },
              title: {
                  text: 'Electricity Monthly Cost ($)'
              },
              xAxis: {
                  categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Max']
              },
              yAxis: {
                  min: 0,
                  title: {
                      text: 'dollars ($) '
                  },
                  stackLabels: {
                      enabled: true,
                      style: {
                          fontWeight: 'bold',
                          color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                      }
                  }
              },
              legend: {
                  align: 'center',
                  x: 0,
                  verticalAlign: 'bottom',
                  y: 0,
                  floating: false,
                  backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColorSolid) || 'white',
                  borderColor: '#CCC',
                  borderWidth: 1,
                  shadow: true
              },
              tooltip: {
                  formatter: function() {
                      return '<b>'+ this.x +'</b><br/>'+
                          this.series.name +': '+ this.y +'<br/>'+
                          'Total: '+ this.point.stackTotal;
                  }
              },
              plotOptions: {
                  column: {
                      stacking: 'normal',
                      dataLabels: {
                          enabled: false,
                          color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
                      }
                  }
              },
              series: [{
                  name: 'Energy Charges ($)',
                  type: 'column',
                  data: <?php echo getRowData($electric_tariff['EnergyCharges (~~$~~)']);?>
              }, {
                  name: 'Demand Charges ($)',
                  type: 'column',
                  data: <?php echo getRowData($electric_tariff['DemandCharges (~~$~~)']);?>
              }, {
                  name: 'Service Changes ($)',
                  type: 'column',
                  data: <?php echo getRowData($electric_tariff['ServiceCharges (~~$~~)']);?>
              },{
                  name: 'Adjustment ($)',
                  type: 'column',
                  data: <?php echo getRowData($electric_tariff['Adjustment (~~$~~)']);?>
              }, {
                  name: 'Taxes',
                  type: 'column',
                  data: <?php echo getRowData($electric_tariff['Taxes (~~$~~)']);?>
              },{
                  name: 'Bill ($)',
                  type: 'spline',
                  marker: {
                      symbol: 'square'
                  },
                  data: <?php echo getRowData($electric_tariff['Total (~~$~~)']);?>
      
              }]
          });
      });

      // monthly natural gas cost chart -->
      $(function () {
          $('#natural_gas_monthly_cost_chart').highcharts({
              chart: {
                  zoomType: 'xy'
              },
              title: {
                  text: 'Natural Gas Monthly Cost ($)'
              },
              xAxis: {
                  categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Max']
              },
              yAxis: {
                  min: 0,
                  title: {
                      text: 'dollars ($) '
                  },
                  stackLabels: {
                      enabled: true,
                      style: {
                          fontWeight: 'bold',
                          color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                      }
                  }
              },
              legend: {
                  align: 'center',
                  x: 0,
                  verticalAlign: 'bottom',
                  y: 0,
                  floating: false,
                  backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColorSolid) || 'white',
                  borderColor: '#CCC',
                  borderWidth: 1,
                  shadow: true
              },
              tooltip: {
                  formatter: function() {
                      return '<b>'+ this.x +'</b><br/>'+
                          this.series.name +': '+ this.y +'<br/>'+
                          'Total: '+ this.point.stackTotal;
                  }
              },
              plotOptions: {
                  column: {
                      stacking: 'normal',
                      dataLabels: {
                          enabled: false,
                          color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
                      }
                  }
              },
              series: [{
                  name: 'Energy Charges ($)',
                  type: 'column',
                  data: <?php echo getRowData($gas_tariff['EnergyCharges (~~$~~)']);?>
              }, {
                  name: 'Demand Charges ($)',
                  type: 'column',
                  data: <?php echo getRowData($gas_tariff['DemandCharges (~~$~~)']);?>
              }, {
                  name: 'Service Changes ($)',
                  type: 'column',
                  data: <?php echo getRowData($gas_tariff['ServiceCharges (~~$~~)']);?>
              },{
                  name: 'Adjustment ($)',
                  type: 'column',
                  data: <?php echo getRowData($gas_tariff['Adjustment (~~$~~)']);?>
              }, {
                  name: 'Taxes',
                  type: 'column',
                  data: <?php echo getRowData($gas_tariff['Taxes (~~$~~)']);?>
              },{
                  name: 'Bill ($)',
                  type: 'spline',
                  marker: {
                      symbol: 'square'
                  },
                  data: <?php echo getRowData($gas_tariff['Total (~~$~~)']);?>
      
              }]
          });
      });
    </script>
  </body>
</html>
