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
    <link rel="stylesheet" type="text/css" href="_styles.css" media="screen">

    <style>
	body {
		background: linear-gradient(to left, #bbb, #fff);
	}

	.container{
		width: 90%;
	}

	.table-striped{
		background: #eee;
	}

	.table{
		text-align: center;
	}

	#Measure-List input[type='checkbox']{
		margin: 5px 5px;
	}

	#filter {
		position: fixed;
		top: 0;
		left: 0;
		background: #111;
		min-width: 100%;
		min-height: 100%;
		opacity: 0.1;
	}

	.progress{
		height: 30px;
	}

	.loading-div {
		z-index: 100;
		position: fixed;
		top: 55%;
		display: none;
		padding: 10px;
		text-align: center;
		margin: 0 20%;
		width: 60%;
		max-width: 800px;
		background: #fff;
	}

    th {
        text-align:left;
    }
    </style>
  </head>

  <body>

        <!-- Navbar
    ================================================== -->
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container" >
          <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="brand pull-left" href="./">EEB Hub Simulation Platform</a>
          <div class="nav-collapse collapse">
            <ul class="nav">
              <li class="">
                <a href="http://tools.eebhub.org/">Home</a>
              </li>
              <li class="">
                <a href="http://tools.eebhub.org/">Lite</a>
              </li>
              <li class="">
                <a href="http://tools.eebhub.org/">Partial</a>
              </li>
              <li class="">
                <a href="http://tools.eebhub.org/substantial">Substantial</a>
              </li>
              <li class="active">
                <a href="http://tools.eebhub.org/comprehensive">Comprehensive</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Container -->
    <div class="container" style="margin-top: 50px;">

        <!-- Sub-Nav-bar -->
        <div class="navbar">
          <div class="navbar-inner">
            <ul class="nav">
			  <li class="active"><a href="eem_measure.php">Measure</a></li>
              <li><a href="tracking-sheet.php">Tracking Sheet</a></li>
              <li><a href="./energy-use.php">Energy</a></li>
              <li><a href="./zone-component-load.php">Zone Loads</a></li>
              <li><a href="./energy-intensity.php">Energy Intensity</a></li>
              <li><a href="./energy-cost.php">Energy Cost</a></li>
            </ul>
          </div>
        </div>
 
    <!-- main content -->
    
        <!-- Measure list -->
        <div id="Measure-List" style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: medium solid #aaa;">
            <h2>Measure List</h2>
            Filter by:
            <input type="checkbox" name="Measure_list[]" value="Cost/sf [range]">Cost/sf [range]</input>
            <input type="checkbox" name="Measure_list[]" value="RX/Comissioning">RX/Comissioning</input> 
            <input type="checkbox" name="Measure_list[]" value="Low/no-cost">Low/no-cost</input>
            <input type="checkbox" name="Measure_list[]" value="Causes Noise/Disruptioin">Causes Noise/Disruption</input>
            <input type="checkbox" name="Measure_list[]" value="Construction Heavy">Construction Heavy</input>
        </div>

        <!-- current field -->
        <div id="tree-menu">
        <ol class="tree ">
        <form name="input" action="simulate-eem.php" method="post" onsubmit="$('#baseline-form, header, .top-nav, footer').fadeTo('slow', 0.33);
                    $('.loading-div').fadeIn('slow');  
                    $('html,body').css('overflow', 'hidden');
                    loading(); 
                    return true; ">
            <!--################################################  Occupancy/Schedule  ###############################-->
            <li>
                <label for="occupancy-schedule">Occupancy/Schedule</label> <input type="checkbox" id="occupancy-schedule" name="selected[]" value="occupancyChecked"/> 
                <ol>
                    <li>
                        <label for="hvac-schedule">HVAC Schedule</label> <input type="checkbox" id="hvac-schedule" name="selected[]" value="hvacScheduleChecked"/> 
                        <ol>
                            <li>
                                <!-- Ventilation Setback -->
                                <label for="turn-off-ventilation">Ventilation Setback</label> <input type="checkbox" id="turn-off-ventilation" name="selected[]" value="ventilationChecked" /> 
                                <ol>
                                    <li>
                                        <div>
                                            <br>
                                                <table border="0" cellpadding="5" style="font-size: 12px;"> 
                                                  <caption></caption>
                                                  <thead>
                                                    <tr>
                                                      <th style="font-size: 15px;">Current:</th>                           
                                                      <th style="width:460px"></th>
                                                      <th style="font-size: 15px;">Proposed:</th>
                                                      <th></th>
                                                      <th></th>
                                                    </tr>
                                                  </thead>

                                                  <tbody>
                                                    <tr>     
                                                      <td>                                     
                                                        <div class="btn-group" data-toggle="buttons-radio">
                                                            <button name="type" type="button" class="btn active" value="current-off" >OFF</button>
                                                            <button name="type" type="button" class="btn" value="current-on" disabled>ON</button>
                                                        </div>
                                                      </td>                                           
                                                      <td>&nbsp;</td>
                                                      <td>
                                                        <div class="btn-group" data-toggle="buttons-radio">
                                                            <button name="type" type="button" class="btn" value="proposed-off" disabled>OFF</button>
                                                            <button name="type" type="button" class="btn active" value="propsed-on" >ON</button>
                                                        </div>
                                                      </td>               
                                                      <td></td>
                                                      <td></td>
                                                    </tr>

                                                    <tr>                                              
                                                      <td></td>
                                                      <td>&nbsp;</td>
                                                      <td></td>      
                                                      <td></td>
                                                      <td></td>
                                                    </tr>

                                                     <tr>                                                         
                                                      <td></td>
                                                      <td>&nbsp;</td>
                                                      <td></td>
                                                      <td><b>Start Hour</b></td>
                                                      <td><b>End Hour</b></td>
                                                    </tr>

                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td>&nbsp;</td>
                                                      <td>Schedule 1:</td>  
                                                      <td><input type="text" name="hvac-current" style="width:50px" value="19:00" disabled></input></td>
                                                      <td> <input type="text" name="hvac-proposed" style="width:50px" value="6:00" disabled></input></td>
                                                    </tr>

                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td>Measure Cost:</td>
                                                      <td>
                                                        <div class="input-prepend input-append">
                                                          <span class="add-on">$</span>
                                                          <input type="text" name="measure" style="width:50px" value="" disabled></input>
                                                          <span class="add-on">.00</span>
                                                        </div>
                                                      </td>
                                                      <td></td>  
                                                    </tr>

                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td>Year of Installation:</td>
                                                      <td><input type="text" name="year" style="width:50px" value="" disabled></input></td>
                                                      <td></td>  
                                                    </tr>

                                                  </tbody>
                                                </table>
                                            </br></br>
                                        </div>
                                    </li>
                                </ol>
                            </li>

                            <!-- HVAC Schedule -> building-management-system  -->
                        <!--    <li>
                                <label for="building-management-system">Building Management System</label> <input type="checkbox" id="building-management-system" name="selected[]" value="bmsChecked"/> 
                                <ol>
                                    <li class="file"><a href="">Subfile 1</a></li>
                                </ol>
                            </li> -->
                        </ol>
                    </li>
                </ol>
            </li>

            <!--################################################  Plug & Process Load  ##############################-->
            <li>
                <label for="plug-load">Plug Load</label> <input type="checkbox" id="plug-load" name="selected[]" value="plugLoadChecked"  /> 
                <ol>
                    <li>
                        <!-- Equipment -->
                        <label for="equipment">Equipment</label> <input type="checkbox" id="equipment" name="selected[]" value="equipmentChecked"   /> 
                        <ol>                    
                            <!-- EnergyStar Equipment  -->
                            <li>
                                <label for="energyStar-equipment">EnergyStar Equipment</label> <input type="checkbox" id="energyStar-equipment" name="selected[]" value="energyStarEquipmentChecked"/> 
                                <ol>
                                    <li>
                                        <div>
                                            <br>
                                                <table border="0" cellpadding="5" style="font-size: 12px;"> 
                                                  <caption></caption>
                                                  <thead>
                                                    <tr>
                                                      <th style="font-size: 15px;">Current:</th>
                                                      <th></th>
                                                      <th style="width:250px"></th>
                                                      <th style="font-size: 15px;">Proposed:</th>
                                                      <th></th>
                                                    </tr>
                                                  </thead>

                                                  <tbody>
                                                    <tr>
                                                      <td>Plug Load Density: </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span2" id="plug-loan-density" type="number" value="1" disabled>
                                                            <span class="add-on"><sup>W</sup>&frasl;<sub>ft<sup>2</sup></sub></span>                                
                                                        </div>
                                                      </td>
                                                     
                                                      <td>&nbsp;</td>

                                                      <td>Plug Load Density: </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span2" id="plug-loan-density" type="number" value="0.85" disabled>
                                                            <span class="add-on"><sup>W</sup>&frasl;<sub>ft<sup>2</sup></sub></span>                                        
                                                        </div>
                                                      </td>
                                                    </tr>

                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td></td> 
                                                      <td>Measure Cost:</td>
                                                      <td>
                                                        <div class="input-prepend input-append">
                                                          <span class="add-on">$</span>
                                                          <input type="text" name="measure-cost" style="width:50px" value="" disabled></input>
                                                          <span class="add-on">.00</span>
                                                        </div>
                                                      </td>
                                                       
                                                    </tr>

                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td></td>  
                                                      <td>Year of Installation:</td>
                                                      <td><input type="text" name="year" style="width:50px" value="" disabled></input></td>
                                                    </tr>

                                                  </tbody>
                                                </table>
                                            </br></br>
                                        </div>
                                    </li>
                                </ol>
                            </li>

                            <!-- Plug Load Control -->
                            <li>
                                <label for="plug-load-control">Plug Load Control</label> <input type="checkbox" id="plug-load-control" name="selected[]" value="plcChecked" /> 
                                <ol>                
                                    <li>
                                        <div>
                                            <br>
                                                <table border="0" cellpadding="5" style="font-size: 12px;"> 
                                                  <caption></caption>
                                                  <thead>
                                                    <tr>
                                                      <th style="font-size: 15px;">Current:</th>
                                                      <th></th>
                                                      <th style="width:270px"></th>
                                                      <th style="font-size: 15px;">Proposed:</th>
                                                      <th></th>
                                                      <th></th>
                                                      <th></th>
                                                      <th></th>
                                                    </tr>
                                                  </thead>

                                                  <tbody>
                                                    <tr>
                                                      <td> </td>
                                                      <td> </td>
                                                      <td>&nbsp;</td>
                                                      <td></td>
                                                      <td></td>
                                                      <td></td>
                                                      <td style="font-size: 10px;"> <b>Start Hour </b></td>
                                                      <td style="font-size: 10px;"> <b>End Hour </b></td>
                                                    </tr>

                                                    <tr>
                                                      <td>Load Density: </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span2" id="load-density" type="number" value="0.5" disabled>
                                                            <span class="add-on"><sup>W</sup>&frasl;<sub>ft<sup>2</sup></sub></span>                                
                                                        </div>
                                                      </td>
                                                      <td>&nbsp;</td>
                                                      <td>Load Density (Weekdays): </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span2" id="load-density-weekdays" type="number" value="0.25" style="width:50px" disabled>
                                                            <span class="add-on"><sup>W</sup>&frasl;<sub>ft<sup>2</sup></sub></span>                                
                                                        </div>
                                                      </td>
                                                      <td>Schedule:</td>
                                                      <td><input type="text" name="load-density-proposed-schedule1" style="width:50px" value="0:00" disabled></input></td>
                                                      <td><input type="text" name="load-density-proposed-schedule2" style="width:50px" value="08:00" disabled></input></td>
                                                    </tr>


                                                    <tr>
                                                      <td></td>
                                                      <td></td>
                                                      <td></td>
                                                      <td>Load Density (Weekends): </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span2" id="load-density-weekends" type="number" value="0.25" style="width:50px" disabled>
                                                            <span class="add-on"><sup>W</sup>&frasl;<sub>ft<sup>2</sup></sub></span>                            
                                                        </div>
                                                      </td>
                                                      <td>Schedule:</td>
                                                      <td><input type="text" name="load-density-proposed-schedule1" style="width:50px" value="0:00" disabled></input></td>
                                                      <td><input type="text" name="load-density-proposed-schedule2" style="width:50px" value="23:59" disabled></input></td>
                                                    </tr>


                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td></td> 
                                                      <td>Measure Cost:</td>
                                                      <td>
                                                        <div class="input-prepend input-append">
                                                          <span class="add-on">$</span>
                                                          <input type="text" name="measure-cost" style="width:50px" value="" disabled></input>
                                                          <span class="add-on">.00</span>
                                                        </div>
                                                      </td>
                                                      <td></td> 
                                                      <td></td> 
                                                      <td></td> 
                                                    </tr>

                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td></td>
                                                      <td>Year of Installation:</td>
                                                      <td><input type="text" name="year" style="width:50px" value="" disabled></input></td> 
                                                      <td></td> 
                                                      <td></td>    
                                                      <td></td> 
                                                    </tr>

                                                  </tbody>
                                                </table>
                                            </br></br>
                                        </div>
                                    </li>           
                                </ol>
                            </li>

                        </ol>
                    </li>

                </ol>
            </li>   

            <!--################################################  Lighting  #########################################-->
            <li>
                <label for="lighting">Lighting</label> <input type="checkbox" id="lighting" name="selected[]" value="lightingChecked" /> 
                <ol>
                    <!-- schedule -->
                    <!--<li>
                        <label for="schedule">Schedule</label> <input type="checkbox" id="schedule" name="selected[]" value="lightingScheduleChecked" /> 
                        <ol>                
                            <li class="file"><a href="">Subfile 1</a></li>              
                        </ol>
                    </li>-->    

                    <!-- Daylighting -->
                    <li>
                        <label for="daylighting">Passive Daylighting</label> <input type="checkbox" id="daylighting" name="selected[]" value="dayLightingChecked" /> 
                        <ol>
                        
                            <!-- Daylight Based Dimming -->
                            <li>
                                <label for="daylight-based-dimming">Daylight Based Dimming</label> <input type="checkbox" id="daylight-based-dimming" name="selected[]" value="daylightDimmingChecked"/> 
                                <ol>
                                <li>        

                                    <div>
                                            <br>
                                                <table border="0" cellpadding="5" style="font-size: 12px;"> 
                                                  <caption></caption>
                                                  <thead>
                                                    <tr>
                                                      <th style="font-size: 15px;">Current:</th>                           
                                                      <th style="width:460px"></th>
                                                      <th style="font-size: 15px;">Proposed:</th>
                                                      <th></th>
                                                      <th></th>
                                                    </tr>
                                                  </thead>

                                                  <tbody>
                                                    <tr>     
                                                      <td>                                     
                                                        <div class="btn-group" data-toggle="buttons-radio">
                                                            <button name="type" type="button" class="btn active" value="current-off" >OFF</button>
                                                            <button name="type" type="button" class="btn" value="current-on" disabled>ON</button>
                                                        </div>
                                                      </td>                                           
                                                      <td>&nbsp;</td>
                                                      <td>
                                                        <div class="btn-group" data-toggle="buttons-radio">
                                                            <button name="type" type="button" class="btn" value="proposed-off" disabled>OFF</button>
                                                            <button name="type" type="button" class="btn active" value="propsed-on" >ON</button>
                                                        </div>
                                                      </td>               
                                                      <td></td>
                                                      <td></td>
                                                    </tr>

                                                    <tr>                                              
                                                      <td></td>
                                                      <td>&nbsp;</td>
                                                      <td></td>      
                                                      <td></td>
                                                      <td></td>
                                                    </tr>

                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td>Measure Cost:</td>
                                                      <td>
                                                        <div class="input-prepend input-append">
                                                          <span class="add-on">$</span>
                                                          <input type="text" name="measure" style="width:50px" value="" disabled></input>
                                                          <span class="add-on">.00</span>
                                                        </div>
                                                      </td>
                                                      <td></td>  
                                                    </tr>

                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td>Year of Installation:</td>
                                                      <td><input type="text" name="year" style="width:50px" value="" disabled></input></td>
                                                      <td></td>  
                                                    </tr>

                                                  </tbody>
                                                </table>
                                            </br></br>
                                    </div>                              
                                </li>   
                                </ol>
                            </li>                   
    
                        </ol>
                    </li>

                    <!-- Active Lighting -->
                    <li>
                        <label for="active-lighting">Active Lighting</label> <input type="checkbox" id="active-lighting" name="selected[]" value="activelightingChecked"/> 
                        <ol>

                            <!-- Occupancy-Based Lighting Sensors -->
                            <li>
                                <label for="occupancy-sensors">Occupancy-Based Lighting Sensors</label> <input type="checkbox" id="occupancy-sensors" name="selected[]" value="oblsChecked" /> 
                                <ol>
                                    <li>
                                        <div>
                                            <br>
                                                <table border="0" cellpadding="5" style="font-size: 12px;"> 
                                                  <caption></caption>
                                                  <thead>
                                                    <tr>
                                                      <th style="font-size: 15px;">Current:</th>                           
                                                      <th style="width:480px"></th>
                                                      <th style="font-size: 15px;">Proposed:</th>
                                                      <th></th>
                                                      <th></th>
                                                    </tr>
                                                  </thead>

                                                  <tbody>
                                                    <tr>     
                                                      <td>                                     
                                                        <div class="btn-group" data-toggle="buttons-radio">
                                                            <button name="type" type="button" class="btn active" value="current-off" >OFF</button>
                                                            <button name="type" type="button" class="btn" value="current-on" disabled>ON</button>
                                                        </div>
                                                      </td>                                           
                                                      <td>&nbsp;</td>
                                                      <td>
                                                        <div class="btn-group" data-toggle="buttons-radio">
                                                            <button name="type" type="button" class="btn" value="proposed-off" disabled>OFF</button>
                                                            <button name="type" type="button" class="btn active" value="propsed-on" >ON</button>
                                                        </div>
                                                      </td>               
                                                      <td></td>
                                                      <td></td>
                                                    </tr>

                                                    <tr>                                              
                                                      <td></td>
                                                      <td>&nbsp;</td>
                                                      <td></td>      
                                                      <td></td>
                                                      <td></td>
                                                    </tr>

                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td>Measure Cost:</td>
                                                      <td>
                                                        <div class="input-prepend input-append">
                                                          <span class="add-on">$</span>
                                                          <input type="text" name="measure-cost" style="width:50px" value="" disabled></input>
                                                          <span class="add-on">.00</span>
                                                        </div>
                                                      </td>
                                                      <td></td>  
                                                    </tr>

                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td>Year of Installation:</td>
                                                      <td><input type="text" name="year" style="width:50px" value="" disabled></input></td>
                                                      <td></td>  
                                                    </tr>

                                                  </tbody>
                                                </table>
                                            </br></br>
                                        </div>
                                    </li>
                                </ol>
                            </li> 

                            <!-- Office Lighting Fixture Upgrade -->
                            <li>
                                <label for="office-lighting">Office Lighting Fixture Upgrade</label> <input type="checkbox" id="office-lighting" name="selected[]" value="OfficefixturedChecked"  /> 
                                <ol>
                                <li>
                                    </br>                               
                                    <table border="0" cellpadding="5" style="font-size: 12px;"> 
                                      <caption></caption>
                                      <thead>
                                        <tr>
                                          <th style="font-size: 15px;">Current:</th>
                                          <th></th>
                                          <th style="width:300px"></th>
                                          <th style="font-size: 15px;">Proposed:</th>
                                          <th></th>
                                        </tr>
                                      </thead>

                                      <tbody>
                                     <!--   
                                        <tr>
                                          <td>W/sf 1.2</td>
                                          <td></td> 
                                          <td></td>
                                          <td> 
                                            <select name="">
                                                <option>Package 1</option>
                                                <option>Package 2</option>
                                                <option>Package 3</option>
                                            </select> 
                                          </td>  
                                          <td> 
                                            <button class="btn">Save to package</button> 
                                            &nbsp;&nbsp;&nbsp;
                                            <button class="btn">Apply to all packages</button>
                                          </td>
    
                                        </tr>

                                        <tr>                                                         
                                          <td>Fixture type:</td>
                                          <td>
                                            <select>
                                                <option>suspended</option>
                                            </select>
                                          </td>  
                                          <td></td>
                                          <td>
                                            <select name="">
                                                <option>[Same]</option>
                                                <option>Change</option>
                                            
                                            </select> 

                                          </td>  
                                          <td></td> 
                                        </tr>

                                        <tr>                                                         
                                          <td>Target lumen level:</td>
                                          <td><input type="number" name="target_lumen_level" value="300" min="0.0"></input></td>  
                                          <td></td>
                                          <td>Measure Cost:</td>  
                                          <td><input name="Measurer-cost" type="number"> </input></td>
                                        </tr>

                                        <tr>                                                         
                                          <td>Ballast factor(0.0-1.0):</td>
                                          <td><input type="number" name="ballast_factor" value="" max="1.0" min="0.0"></td>  
                                          <td></td>
                                          <td>Override: </td>  
                                          <td><input name="override" type="number"> </input></td>
                                        </tr>
                                     
                                        <tr>                                                         
                                          <td><b>Planned Capital & Maintenance</b></td>
                                          <td></td>
                                          <td></td>  
                                          <td><b>Planned Capital & Maintenance</b></td>  
                                          <td></td>
                                        </tr>

                                        <tr>                                                         
                                          <td>Expected Replacement Date(mm/dd/yy)</td>
                                          <td><input type="date" name="expected_replacement_date" value="" ></td> 
                                          <td></td> 
                                          <td>Expected Replacement Date:</td>
                                          <td><input type="date" name="expexted-replacement-date"> </input></td>  
                                          
                                        </tr>

                                        <tr>                                                         
                                          <td>Expected Replacement Cost($):</td>
                                          <td><input type="number" name="expected_replacement_cost" value="1500" min="0.0"></td>  
                                          <td></td>
                                          <td>Expected Yearly Maintenance Cost:</td>  
                                          <td></td>
                                        </tr>

                                        <tr>                                                         
                                          <td>Yearly Maintenance Cost($):</td>
                                          <td><input type="number" name="yearly_maintenance_cost" value="30" min="0.0"></td>  
                                          <td></td>
                                          <td>Overide:</td>  
                                          <td><input type="number" name="expexted-yearly-maintenance-cost" value="24"> </input></td>
                                        </tr>

                                        <tr>                                                         
                                          <td>Replacement Life(yrs):</td>
                                          <td><input type="number" name="replacement_life" value="20" min="0.0"></input></td>  
                                          <td></td>
                                          <td>Measurer Life(yrs):</td>  
                                          <td><input type="number" name="expexted-yearly-maintenance-cost" value="20"> </input></td>
                                        </tr>

                                        <tr>                                                         
                                          <td></td>
                                          <td></td>  
                                          <td></td>
                                          <td>Embodied energy of retrofit materials(GJ):</td>  
                                          <td><input type="number" name="expexted-yearly-maintenance-cost" value="24.3"> </input></td>
                                        </tr>
                                    -->


                                        <tr>
                                          <td>Lamps:</td>
                                          <td>
                                            <div class="input-append">
                                                <input class="span2" id="lamp-current" type="number" value="40" disabled>
                                                <span class="add-on">W</span>                               
                                            </div>
                                          </td> 
                                          <td></td>
                                          <td>Lamps: </td>
                                          <td> 
                                            <div class="input-append">
                                                <input class="span2" id="lamp-proposed" type="number" value="28" disabled>
                                                <span class="add-on">W</span>                               
                                            </div>
                                          </td>  
                                        </tr>

                                        <tr>                                                         
                                          <td></td>  
                                          <td></td> 
                                          <td></td>  
                                          <td>Measure Cost:</td>
                                          <td>
                                            <div class="input-prepend input-append">
                                              <span class="add-on">$</span>
                                              <input type="text" name="office-measure-cost" style="width:50px" value="" disabled></input>
                                              <span class="add-on">.00</span>
                                            </div>
                                          </td>
                                        </tr>

                                        <tr>                                                         
                                          <td></td>  
                                          <td></td> 
                                          <td></td> 
                                          <td>Year of Installation:</td>
                                          <td><input type="text" name="office-year" style="width:50px" value="" disabled></input></td> 
                                        </tr>
                                      </tbody>
                                    </table>
                                    </br></br></br>
                                </li>   
                                </ol>
                            </li>

                            <!-- Bathroom Lighting Fixture Upgrade -->
                            <li>
                                <label for="bathroom-lighting">Bathroom Lighting Fixture Upgrade</label> <input type="checkbox" id="bathroom-lighting" name="selected[]" value="bathroomFixturedChecked"  /> 
                                <ol>
                                <li>
                                    </br>                               
                                    <table border="0" cellpadding="5" style="font-size: 12px;"> 
                                      <caption></caption>
                                      <thead>
                                        <tr>
                                          <th style="font-size: 15px;">Current:</th>
                                          <th></th>
                                          <th style="width:300px"></th>
                                          <th style="font-size: 15px;">Proposed:</th>
                                          <th></th>
                                        </tr>
                                      </thead>

                                      <tbody>
                                        <tr>
                                          <td>Lamps:</td>
                                          <td>
                                            <div class="input-append">
                                                <input class="span2" id="lamp-current" type="number" value="40" disabled>
                                                <span class="add-on">W</span>                               
                                            </div>
                                          </td> 
                                          <td></td>
                                          <td>Lamps: </td>
                                          <td> 
                                            <div class="input-append">
                                                <input class="span2" id="lamp-proposed" type="number" value="28" disabled>
                                                <span class="add-on">W</span>                               
                                            </div>
                                          </td>  
                                        </tr>

                                        <tr>                                                         
                                          <td></td>  
                                          <td></td> 
                                          <td></td> 
                                          <td>Measure Cost:</td>
                                          <td>
                                            <div class="input-prepend input-append">
                                              <span class="add-on">$</span>
                                              <input type="text" name="bathroom-measure-cost" style="width:50px" value="" disabled></input>
                                              <span class="add-on">.00</span>
                                            </div>
                                          </td> 
                                        </tr>

                                        <tr>                                                         
                                          <td></td>  
                                          <td></td> 
                                          <td></td>  
                                          <td>Year of Installation:</td>
                                          <td><input type="text" name="bathroom-year" style="width:50px" value="" disabled></input></td>
                                        </tr>
                                      </tbody>
                                    </table>
                                    </br></br></br>
                                </li>   
                                </ol>
                            </li>

                            <!-- Emergency Lighting Upgrade -->
                            <li>
                                <label for="emergency-lighting">Emergency Lighting Upgrade</label> <input type="checkbox" id="emergency-lighting" name="selected[]" value="emergencyLightingChecked"  /> 
                                <ol>
                                <li>
                                    </br>                               
                                    <table border="0" cellpadding="5" style="font-size: 12px;"> 
                                      <caption></caption>
                                      <thead>
                                        <tr>
                                          <th style="font-size: 15px;">Current:</th>
                                          <th></th>
                                          <th style="width:280px"></th>
                                          <th style="font-size: 15px;">Proposed:</th>
                                          <th></th>
                                        </tr>
                                      </thead>

                                      <tbody>
                                        <tr>
                                          <td>Type:</td>
                                          <td>
                                            <select name="" disabled>
                                                <option>CFL</option>
                                            </select> 
                                          </td> 
                                          <td></td>
                                          <td>Type: </td>
                                           <td>
                                            <select name="" disabled>
                                                <option>LED</option>
                                            </select> 
                                          </td>  
                                        </tr>

                                        <tr>                                                         
                                          <td></td>  
                                          <td></td> 
                                          <td></td> 
                                          <td>Measure Cost:</td>
                                          <td>
                                            <div class="input-prepend input-append">
                                              <span class="add-on">$</span>
                                              <input type="text" name="emergencing-measure-cost" style="width:50px" value="" disabled></input>
                                              <span class="add-on">.00</span>
                                            </div>
                                          </td> 
                                        </tr>

                                        <tr>                                                         
                                          <td></td>  
                                          <td></td> 
                                          <td></td>  
                                          <td>Year of Installation:</td>
                                          <td><input type="text" name="emergencing-year" style="width:50px" value="" disabled></input></td>
                                        </tr>
                                      </tbody>
                                    </table>
                                    </br></br></br>
                                </li>   
                                </ol>
                            </li>

                        </ol>
                    </li>

                </ol>
            </li>

           <!--################################################  Enclosure  ########################################-->
            <li>
                <label for="enclosure">Enclosure</label> <input type="checkbox" id="enclosure" name="selected[]" value="enclosureChecked" /> 
                <ol>
                    <!-- Roof -->
                    <li>
                        <label for="roof">Roof</label> <input type="checkbox" id="roof" name="selected[]" value="roofChecked"/> 
                        <ol>
                            <!-- Increase Roof Insulation, R-10 -->
                            <li>
                                <label for="increase-roof-insulation">Increase Roof Insulation, R-10</label> <input type="checkbox" id="increase-roof-insulation" name="selected[]" value="roofInsulationChecked"  /> 
                                <ol>            
                                    <li>
                                        <div>
                                            <br>
                                                <table border="0" cellpadding="5" style="font-size: 12px;"> 
                                                  <caption></caption>
                                                  <thead>
                                                    <tr>
                                                      <th style="font-size: 15px;">Current:</th>
                                                      <th></th>   
                                                      <th style="width:180px"></th>
                                                      <th style="font-size: 15px;">Proposed (+R-10):</th>
                                                      <th></th>
                                                    </tr>
                                                  </thead>

                                                  <tbody>
                                                    <tr>
                                                      <td>Roof Insulation (R value): </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span2" id="roof-insulation-current" type="number" value="30" disabled>
                                                            <span class="add-on">
                                                                <sup>h ft<sup>2</sup> &deg;F</sup>&frasl;<sub>Btu</sub>
                                                            </span>     
                                                        </div> 
                                                      </td>
                                                      <td>&nbsp;</td>
                                                      <td>Roof Insulation (R value): </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span2" id="roof-insulation-proposed" type="number" value="10" disabled>
                                                            <span class="add-on">
                                                                <sup>h ft<sup>2</sup> &deg;F</sup>&frasl;<sub>Btu</sub>
                                                            </span>                                 
                                                        </div> 
                                                      </td>
                                                    </tr>

                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td></td> 
                                                      <td>Measure Cost:</td>
                                                      <td>
                                                        <div class="input-prepend input-append">
                                                          <span class="add-on">$</span>
                                                          <input type="text" name="measure" style="width:50px" value="" disabled></input>
                                                          <span class="add-on">.00</span>
                                                        </div>
                                                      </td>
                                                       
                                                    </tr>

                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td></td>  
                                                      <td>Year of Installation:</td>
                                                      <td><input type="text" name="year" style="width:50px" value="" disabled></input></td>
                                                    </tr>


                                                  </tbody>
                                                </table>
                                            </br></br>
                                        </div>
                                        <br>        
                                    </li>

                                </ol>
                            </li>
                        </ol>
                    </li>

                    <!-- Exterior Wall -->
                    <li>
                        <label for="exterior-wall">Exterior Wall</label> <input type="checkbox" id="exterior-wall" name="selected[]" value="exteriorWallChecked"  /> 
                        <ol>
                            <!-- Increase Wall Insulation, R-10 -->
                            <li>
                                <label for="increase-wall-insulation-1">Increase Wall Insulation, R-10</label> <input type="checkbox" id="increase-wall-insulation-1" name="selected[]" value="wallInsulation1Checked"  /> 
                                <ol>
                                    <li>
                                        <div>
                                            <br>
                                                <table border="0" cellpadding="5" style="font-size: 12px;"> 
                                                  <caption></caption>
                                                  <thead>
                                                    <tr>
                                                      <th style="font-size: 15px;">Current:</th>
                                                      <th></th>
                                                      <th style="width:180px"></th>
                                                      <th style="font-size: 15px;">Proposed (+R-10):</th>
                                                      <th></th>
                                                    </tr>
                                                  </thead>

                                                  <tbody>
                                                    <tr>
                                                      <td>Wall Insulation (R value): </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span2" id="wall-insulation-1" type="number" value="3.0906" disabled>
                                                            <span class="add-on">
                                                                <sup>h ft<sup>2</sup> &deg;F</sup>&frasl;<sub>Btu</sub>
                                                            </span>     
                                                        </div> 
                                                      </td>
                                                      <td>&nbsp;</td>
                                                      <td>Wall Insulation (R value): </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span2" id="wall-insulation-1" type="number" value="10.0" disabled>
                                                            <span class="add-on">
                                                                <sup>h ft<sup>2</sup> &deg;F</sup>&frasl;<sub>Btu</sub>
                                                            </span>                                 
                                                        </div> 
                                                      </td>
                                                    </tr>

                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td></td> 
                                                      <td>Measure Cost:</td>
                                                      <td>
                                                        <div class="input-prepend input-append">
                                                          <span class="add-on">$</span>
                                                          <input type="text" name="measure" style="width:50px" value="" disabled></input>
                                                          <span class="add-on">.00</span>
                                                        </div>
                                                      </td>
                                                       
                                                    </tr>

                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td></td>  
                                                      <td>Year of Installation:</td>
                                                      <td><input type="text" name="year" style="width:50px" value="" disabled></input></td>
                                                    </tr>
                                                  </tbody>
                                                </table>
                                            </br></br>
                                        </div>
                                        <br>        
                                    </li>
                                </ol>
                            </li>

                            <!-- Increase Wall Insulation, R-20 -->
                            <li>
                                <label for="increase-wall-insulation-2">Increase Wall Insulation, R-20</label> <input type="checkbox" id="increase-wall-insulation-2" name="selected[]" value="wallInsulation2Checked"  /> 
                                <ol>
                                    <li>
                                        <div>
                
                                            <br>
                                                <table border="0" cellpadding="5" style="font-size: 12px;"> 
                                                  <caption></caption>
                                                  <thead>
                                                    <tr>
                                                      <th style="font-size: 15px;">Current:</th>
                                                      <th></th>
                                                      <th style="width:180px"></th>
                                                      <th style="font-size: 15px;">Proposed (+R-20):</th>
                                                      <th></th>
                                                    </tr>
                                                  </thead>

                                                  <tbody>
                                                    <tr>
                                                      <td>Wall Insulation (R value): </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span2" id="wall-insulation-2" type="number" value="3.09060" disabled>
                                                            <span class="add-on">
                                                                <sup>h ft<sup>2</sup> &deg;F</sup>&frasl;<sub>Btu</sub>
                                                            </span>                                 
                                                        </div> 
                                                      </td>
                                                      <td>&nbsp;</td>
                                                      <td>Wall Insulation (R value): </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span2" id="wall-insulation-2" type="number" value="20.0" disabled>
                                                            <span class="add-on">
                                                                <sup>h ft<sup>2</sup> &deg;F</sup>&frasl;<sub>Btu</sub>
                                                            </span>                             
                                                        </div> 
                                                      </td>
                                                    </tr>

                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td></td> 
                                                      <td>Measure Cost:</td>
                                                      <td>
                                                        <div class="input-prepend input-append">
                                                          <span class="add-on">$</span>
                                                          <input type="text" name="measure" style="width:50px" value="" disabled></input>
                                                          <span class="add-on">.00</span>
                                                        </div>
                                                      </td>
                                                       
                                                    </tr>

                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td></td>  
                                                      <td>Year of Installation:</td>
                                                      <td><input type="text" name="year" style="width:50px" value="" disabled></input></td>
                                                    </tr>


                                                  </tbody>
                                                </table>
                                            </br></br>
                                        </div>
                                        <br>    
                                    </li>

                                </ol>
                            </li>
                        </ol>
                    </li>


                    <!-- Fenestration -->
                    <li>
                        <label for="Fenestration">Fenestration</label> <input type="checkbox" id="Fenestration" name="selected[]" value="fenestrationChecked"   /> 
                        <ol>
                            <!-- Window Upgrade -->
                            <li>
                                <label for="windows-upgrade">Window Upgrade</label> <input type="checkbox" id="windows-upgrade" name="selected[]" value="windowsUpgradelChecked" /> 
                                <ol>
                                    <li>
                                        <div>
                                            <br>
                                                <table border="0" cellpadding="5" style="font-size: 12px;"> 
                                                  <caption></caption>
                                                  <thead>
                                                    <tr>
                                                      <th style="font-size: 15px;">Current:</th>
                                                      <th></th>
                                                      <th style="width:150px"></th>
                                                      <th style="font-size: 15px;">Proposed (Windows Replacement):</th>
                                                      <th></th>
                                                    </tr>
                                                  </thead>

                                                  <tbody>
                                                    <tr>
                                                      <td>Windows Insulation (U value): </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span2" id="windows-insulation" type="number" value="0.475" disabled>
                                                            <span class="add-on">
                                                                <sup>Btu</sup>&frasl;<sub>h &deg;F </sub><sub>ft<sup>2</sup></sub>
                                                            </span>                                 
                                                        </div> 
                                                      </td>                 
                                                      <td>&nbsp;</td>
                                                      <td>Windows Insulation (U value): </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span2" id="windows-insulation" type="number" value="0.2360" disabled>
                                                            <span class="add-on">
                                                                <sup>Btu</sup>&frasl;<sub>h &deg;F </sub><sub>ft<sup>2</sup></sub>
                                                            </span>                                 
                                                        </div> 
                                                      </td>
                                                    </tr>

                                                    <tr>
                                                      <td>Windows SHGC: </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span1" id="windows-SHGC" type="number" value="0.7" disabled><span class="add-on">[-]</span>                                   
                                                        </div> 
                                                      </td>
                                                      <td>&nbsp;</td>
                                                      <td>Windows SHGC: </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span1" id="windows-SHGC" type="number" value="0.35" disabled><span class="add-on">[-]</span>                                  
                                                        </div> 
                                                      </td>
                                                    </tr>

                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td></td> 
                                                      <td>Measure Cost:</td>
                                                      <td>
                                                        <div class="input-prepend input-append">
                                                          <span class="add-on">$</span>
                                                          <input type="text" name="measure" style="width:50px" value="" disabled></input>
                                                          <span class="add-on">.00</span>
                                                        </div>
                                                      </td>    
                                                    </tr>

                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td></td>  
                                                      <td>Year of Installation:</td>
                                                      <td><input type="text" name="year" style="width:50px" value="" disabled></input></td>
                                                    </tr>

                                                  </tbody>
                                                </table>
                                            </br></br>
                                        </div>
                                        <br>    
                                    </li>
                                </ol>
                            </li>

                            <!-- Windows Film Replacement -->
                            <li>
                                <label for="windows-film-replacement">Windows Film Replacement</label> <input type="checkbox" id="windows-film-replacement" name="selected[]" value="windowsFilmChecked" /> 
                                <ol>
                                    <li>
                                        <div>
                                            <br>
                                                <table border="0" cellpadding="5" style="font-size: 12px;"> 
                                                  <caption></caption>
                                                  <thead>
                                                    <tr>
                                                      <th style="font-size: 15px;">Current:</th>
                                                      <th></th>
                                                      <th style="width:150px"></th>
                                                      <th style="font-size: 15px;">Proposed (Windows Film Replacement):</th>
                                                      <th></th>
                                                    </tr>
                                                  </thead>

                                                  <tbody>
                                                    <tr>
                                                      <td>Windows Insulation (U value): </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span2" id="windows-insulation" type="number" value="0.475" disabled>
                                                            <span class="add-on">
                                                                <sup>Btu</sup>&frasl;<sub>h &deg;F </sub><sub>ft<sup>2</sup></sub>
                                                            </span>                                 
                                                        </div> 
                                                      </td>                 
                                                      <td>&nbsp;</td>
                                                      <td>Windows Insulation (U value): </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span2" id="windows-insulation" type="number" value="0.475" disabled>
                                                            <span class="add-on">
                                                                <sup>Btu</sup>&frasl;<sub>h &deg;F </sub><sub>ft<sup>2</sup></sub>
                                                            </span>                                 
                                                        </div> 
                                                      </td>
                                                    </tr>

                                                    <tr>
                                                      <td>Windows SHGC: </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span1" id="windows-SHGC" type="number" value="0.7" disabled><span class="add-on">[-]</span>                                   
                                                        </div> 
                                                      </td>
                                                      <td>&nbsp;</td>
                                                      <td>Windows SHGC: </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span1" id="windows-SHGC" type="number" value="0.35" disabled><span class="add-on">[-]</span>                                  
                                                        </div> 
                                                      </td>
                                                    </tr>

                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td></td> 
                                                      <td>Measure Cost:</td>
                                                      <td>
                                                        <div class="input-prepend input-append">
                                                          <span class="add-on">$</span>
                                                          <input type="text" name="measure" style="width:50px" value="" disabled></input>
                                                          <span class="add-on">.00</span>
                                                        </div>
                                                      </td>    
                                                    </tr>

                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td></td>  
                                                      <td>Year of Installation:</td>
                                                      <td><input type="text" name="year" style="width:50px" value="" disabled></input></td>
                                                    </tr>

                                                  </tbody>
                                                </table>
                                            </br></br>
                                        </div>
                                        <br>    
                                    </li>
                                </ol>
                            </li>
                        </ol>
                    </li>

                    <!-- Infiltration -->
                    <li>
                        <label for="infiltration">Infiltration</label> <input type="checkbox" id="infiltration" name="selected[]" value="infiltrationChecked"  /> 
                        <ol>
                            <li>
                                <!-- Door Weatherization -->
                                <label for="doorWeatherization">Door Weatherization</label> <input type="checkbox" id="doorWeatherization" name="selected[]" value="doorWeatherizationChecked" />               
                                <ol>
                                    <li>
                                        <div>
                                            <br>
                                                <table border="0" cellpadding="5" style="font-size: 12px;"> 
                                                  <caption></caption>
                                                  <thead>
                                                    <tr>
                                                      <th style="font-size: 15px;">Current:</th>
                                                      <th></th>
                                                      <th style="width:220px"></th>
                                                      <th style="font-size: 15px;">Proposed:</th>
                                                      <th></th>
                                                    </tr>
                                                  </thead>

                                                  <tbody>
                                                    <tr>
                                                      <td>Air Change Per Hour: </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span2" id="infiltration" type="number" value="1.8" disabled>
                                                            <span class="add-on"><sup>1</sup>&frasl;<sub>hr</sub></span>                                
                                                        </div>
                                                      </td>
                                                      <td>&nbsp;</td>
                                                      <td>Air Change Per Hour: </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span2" id="infiltration" type="number" value="1.7" disabled>
                                                            <span class="add-on"><sup>1</sup>&frasl;<sub>hr</sub></span>                                    
                                                        </div>
                                                      </td>
                                                    </tr>

                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td></td> 
                                                      <td>Measure Cost:</td>
                                                      <td>
                                                        <div class="input-prepend input-append">
                                                          <span class="add-on">$</span>
                                                          <input type="text" name="measure" style="width:50px" value="" disabled></input>
                                                          <span class="add-on">.00</span>
                                                        </div>
                                                      </td>
                                                       
                                                    </tr>

                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td></td>  
                                                      <td>Year of Installation:</td>
                                                      <td><input type="text" name="year" style="width:50px" value="" disabled></input></td>
                                                    </tr>
                                                  </tbody>
                                                </table>
                                            </br></br>
                                        </div>
                                    </li>
                                </ol>
                            </li>   

                            <!-- Enclosure Recommissioning -->
                            <li>
                                <label for="enclosureRecommis">Enclosure Recommissioning</label> <input type="checkbox" id="enclosureRecommis" name="selected[]" value="enclosureRecommisChecked" />    
                                <ol>
                                    <li>
                                        <div>
                                            <br>
                                                <table border="0" cellpadding="5" style="font-size: 12px;"> 
                                                  <caption></caption>
                                                  <thead>
                                                    <tr>
                                                      <th style="font-size: 15px;">Current:</th>
                                                      <th></th>
                                                      <th style="width:220px"></th>
                                                      <th style="font-size: 15px;">Proposed:</th>
                                                      <th></th>
                                                    </tr>
                                                  </thead>

                                                  <tbody>
                                                    <tr>
                                                      <td>Air Change Per Hour: </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span2" id="infiltration" type="number" value="1.8" disabled>
                                                            <span class="add-on"><sup>1</sup>&frasl;<sub>hr</sub></span>                                
                                                        </div>
                                                      </td>
                                                      <td>&nbsp;</td>
                                                       <td>Air Change Per Hour: </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span2" id="infiltration" type="number" value="1.35" disabled>
                                                            <span class="add-on"><sup>1</sup>&frasl;<sub>hr</sub></span>                                        
                                                        </div>
                                                      </td>
                                                    </tr>

                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td></td> 
                                                      <td>Measure Cost:</td>
                                                      <td>
                                                        <div class="input-prepend input-append">
                                                          <span class="add-on">$</span>
                                                          <input type="text" name="measure" style="width:50px" value="" disabled></input>
                                                          <span class="add-on">.00</span>
                                                        </div>
                                                      </td>
                                                       
                                                    </tr>

                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td></td>  
                                                      <td>Year of Installation:</td>
                                                      <td><input type="text" name="year" style="width:50px" value="" disabled></input></td>
                                                    </tr>
                                                  </tbody>
                                                </table>
                                            </br></br>
                                        </div>
                                    </li>

                                </ol>
                            </li>


                        </ol>
                    </li>

                </ol>
            </li>

            <!--################################################  HVAC  #############################################-->
            <li>
                <label for="hvac">HVAC</label> <input type="checkbox" id="hvac"name="selected[]" value="hvacChecked" /> 
                <ol>
                    <!-- Schedule -->
                    <!-- <li>
                        <label for="HVAC-schedule">Schedule</label> <input type="checkbox" id="HVAC-schedule" name="selected[]" value="hvacSchedChecked"/> 
                        <ol>                
                            <li class="file"><a href="">Subfile 1</a></li>      
                        </ol>
                    </li> -->

                    <!-- Heating -->
                    <li>
                        <label for="heating">Heating</label> <input type="checkbox" id="heating" name="selected[]" value="heatingChecked"/> 
                        <ol>

                                <!-- Setting Points -->
                            <li>
                                <label for="heating-set-points">Thermostat Setpoints</label> <input type="checkbox" id="heating-set-points" name="selected[]" value="heatingSPChecked" /> 
                                <ol>
                                    <!-- Heating Set Back -->
                                    <li>
                                        <!-- Heating Set Back -->
                                        <label for="heating-set-back" title="Set heating set point, setback, match to schedule">
                                            Heating Set Back
                                        </label> <input type="checkbox" id="heating-set-back" name="selected[]" value="heatingSBChecked"  /> 
                                        <ol>
                                            <li>
                                                <div>                               
                                                    </br>
                                                    <table border="0" cellpadding="5" style="font-size: 11px;"> 
                                                      <caption></caption>
                                                      <thead>
                                                        <tr>
                                                          <th style="font-size: 15px;">Current:</th>
                                                          <th></th>
                                                          <th></th>
                                                          <th></th>
                                                          <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                                          <th></th>
                                                          <th style="font-size: 15px;">Proposed:</th>
                                                          <th></th>
                                                          <th></th>
                                                          <th></th>
                                                          <th></th>
                                                        </tr>

                                                         <tr>
                                                          <th></th>
                                                          <th></th>
                                                          <th></th>
                                                          <th style="font-size: 10px;">Start Hour</th>
                                                          <th style="font-size: 10px;">End Hour</th>
                                                          <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                                          <th></th>
                                                          <th></th>
                                                          <th></th>
                                                          <th style="font-size: 10px;">Start Hour</th>
                                                          <th style="font-size: 10px;">End Hour</th>
                                                        </tr>
                                                      </thead>

                                                      <tbody>
                                                        <tr>
                                                          <td>Heating Set Point Temperature 1: </td>
                                                          <td>
                                                            <div class="input-append">
                                                                <input class="span2" id="heating-set-point" type="number" value="70" style="width:50px" disabled><span class="add-on">&deg;F</span>                                 
                                                            </div>
                                                          </td>
                                                          <td>Heating Schedule 1:</td>
                                                          <td><input type="text" name="heating-schedule" style="width:50px" value="0:00" disabled></input></td>
                                                          <td><input type="text" name="heating-schedule" style="width:50px" value="07:00" disabled></input></td>

                                                          <td>&nbsp;</td>

                                                          <td>Heating Set Point Temperature 1: </td>
                                                          <td>
                                                            <div class="input-append">
                                                                <input class="span2" id="heating-set-point" type="number" value="55" style="width:50px" disabled><span class="add-on">&deg;F</span>                                 
                                                            </div>
                                                          </td>
                                                          <td>Heating Schedule 1:</td>
                                                          <td><input type="text" name="heating-schedule" style="width:50px" value="0:00" disabled></input></td>
                                                          <td><input type="text" name="heating-schedule" style="width:50px" value="07:00" disabled></input></td>
                                                        </tr>

                                                        <tr>
                                                          <td>Heating Set Point Temperature 2: </td>
                                                          <td>
                                                            <div class="input-append">
                                                                <input class="span2" id="heating-set-point" type="number" value="70" style="width:50px" disabled><span class="add-on">&deg;F</span>                                 
                                                            </div>
                                                          </td>
                                                          <td>Heating Schedule 2:</td>
                                                          <td><input type="text" name="heating-schedule" style="width:50px" value="07:00" disabled></input></td>
                                                          <td><input type="text" name="heating-schedule" style="width:50px" value="19:00" disabled></input></td>

                                                          <td>&nbsp;</td>

                                                          <td>Heating Set Point Temperature 2: </td>
                                                          <td>
                                                            <div class="input-append">
                                                                <input class="span2" id="heating-set-point" type="number" value="70" style="width:50px" disabled><span class="add-on">&deg;F</span>                                 
                                                            </div>
                                                          </td>
                                                          <td>Heating Schedule 2:</td>
                                                          <td><input type="text" name="heating-schedule" style="width:50px" value="07:00" disabled></input></td>
                                                          <td><input type="text" name="heating-schedule" style="width:50px" value="19:00" disabled></input></td>
                                                        </tr>


                                                        <tr>
                                                          <td>Heating Set Point Temperature 3: </td>
                                                          <td>
                                                            <div class="input-append">
                                                                <input class="span2" id="heating-set-point" type="number" value="70" style="width:50px" disabled><span class="add-on">&deg;F</span>                                 
                                                            </div>
                                                          </td>
                                                          <td>Heating Schedule 3:</td>
                                                          <td><input type="text" name="heating-schedule" style="width:50px" value="19:00" disabled></input></td>
                                                          <td><input type="text" name="heating-schedule" style="width:50px" value="24:00" disabled></input></td>

                                                          <td>&nbsp;</td>

                                                          <td>Heating Set Point Temperature 3: </td>
                                                          <td>
                                                            <div class="input-append">
                                                                <input class="span2" id="heating-set-point" type="number" value="55" style="width:50px" disabled><span class="add-on">&deg;F</span>                                 
                                                            </div>
                                                          </td>
                                                          <td>Heating Schedule 3:</td>
                                                          <td><input type="text" name="heating-schedule" style="width:50px" value="19:00" disabled></input></td>
                                                          <td><input type="text" name="heating-schedule" style="width:50px" value="24:00" disabled></input></td>
                                                        </tr>
                                                      </tbody>
                                                    </table>

                                                    <hr></br>

                                                    <table border="0" cellpadding="5" style="font-size: 11px;"> 
                                                      <tbody>
                                                        <tr>
                                                          <td></td>  
                                                          <td></td> 
                                                          <td></td>     
                                                          <td></td>  
                                                          <td></td> 
                                                          <td></td>                                                      
                                                          <td></td>  
                                                          <td></td> 
                                                          <td style="width:480px"></td> 
                                                          <td>Measure Cost:</td>
                                                          <td>
                                                            <div class="input-prepend input-append">
                                                              <span class="add-on">$</span>
                                                              <input type="text" name="measure" style="width:50px" value="" disabled></input>
                                                              <span class="add-on">.00</span>
                                                            </div>
                                                          </td>
                                                        </tr>

                                                        <tr>    
                                                          <td></td>  
                                                          <td></td> 
                                                          <td></td>     
                                                          <td></td>  
                                                          <td></td> 
                                                          <td></td>                                                      
                                                          <td></td>  
                                                          <td></td> 
                                                          <td></td> 
                                                          <td>Year of Installation:</td>
                                                          <td><input type="text" name="year" style="width:50px" value="" disabled></input></td>
                                                        </tr>
                                                      </tbody>
                                                    </table>

                                                </div>
                                            </li>
                                        </ol>
                                    </li>

                                </ol>
                            </li>   


                            <li>
                                <label for="condensingBoiler">Condesing Boiler </label> <input type="checkbox" id="condensingBoiler" name="selected[]" value="condensingBoilerChecked" /> 
                                <ol>
                                    <li>

                                        <div>
                                            <br>
                                                <table border="0" cellpadding="5" style="font-size: 12px;"> 
                                                  <caption></caption>
                                                  <thead>
                                                    <tr>
                                                      <th style="font-size: 15px;">Current:</th>
                                                      <th></th>
                                                      <th style="width:250px"></th>
                                                      <th style="font-size: 15px;">Proposed:</th>
                                                      <th></th>
                                                    </tr>
                                                  </thead>

                                                  <tbody>
                                                    <tr>
                                                      <td>Nominal Efficiency: </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span2" id="nominal-efficiency" type="number" value="0.83" disabled>
                                                            <span class="add-on">[-]</span>                             
                                                        </div>
                                                      </td>
                                                     
                                                      <td>&nbsp;</td>

                                                      <td>Nominal Efficiency: </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span2" id="plug-loan-density" type="number" value="0.95" disabled>
                                                            <span class="add-on">[-]</span>                                     
                                                        </div>
                                                      </td>
                                                    </tr>

                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td></td> 
                                                      <td>Measure Cost:</td>
                                                      <td>
                                                        <div class="input-prepend input-append">
                                                          <span class="add-on">$</span>
                                                          <input type="text" name="nominal-efficiency-measure-cost" style="width:50px" value="" disabled></input>
                                                          <span class="add-on">.00</span>
                                                        </div>
                                                      </td>
                                                       
                                                    </tr>

                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td></td>  
                                                      <td>Year of Installation:</td>
                                                      <td><input type="text" name="nominal-efficiency-year" style="width:50px" value="" disabled></input></td>
                                                    </tr>

                                                  </tbody>
                                                </table>
                                            </br></br>
                                        </div>

                                    </li>
                                </ol>
                            </li>

                        </ol>
                    </li>

                    <!-- Cooling -->
                    <li>
                        <label for="cooling">Cooling</label> <input type="checkbox" id="cooling" name="selected[]" value="coolingChecked"/> 
                        <ol>
                            <!-- Setting Points -->
                            <li>
                                <label for="cooling-set-points">Thermostat Setpoints</label> <input type="checkbox" id="cooling-set-points" name="selected[]" value="coolingSPChecked" /> 
                                <ol>
                                    <!-- Cooling Set Back -->
                                    <li>
                                        <label for="cooling-set-back">Cooling Set Back</label> <input type="checkbox" id="cooling-set-back" name="selected[]" value="coolingSBChecked"  /> 
                                        <ol>
                                            <li>
                                                <div>

                                                    </br>
                                                    <table border="0" cellpadding="5" style="font-size: 11px;"> 
                                                      <caption></caption>
                                                      <thead>
                                                        <tr>
                                                          <th style="font-size: 15px;">Current:</th>
                                                          <th></th>
                                                          <th></th>
                                                          <th></th>
                                                          <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                                          <th></th>
                                                          <th style="font-size: 15px;">Proposed:</th>
                                                          <th></th>
                                                          <th></th>
                                                          <th></th>
                                                          <th></th>
                                                        </tr>

                                                         <tr>
                                                          <th></th>
                                                          <th></th>
                                                          <th></th>
                                                          <th style="font-size: 10px;">Start Hour</th>
                                                          <th style="font-size: 10px;">End Hour</th>
                                                          <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                                          <th></th>
                                                          <th></th>
                                                          <th></th>
                                                          <th style="font-size: 10px;">Start Hour</th>
                                                          <th style="font-size: 10px;">End Hour</th>
                                                        </tr>
                                                      </thead>

                                                      <tbody>
                                                        <tr>
                                                          <td>Cooling Set Point Temperature 1: </td>
                                                          <td>
                                                            <div class="input-append">
                                                                <input class="span2" id="cooling-set-point" type="number" value="75" style="width:50px" disabled><span class="add-on">&deg;F</span>                                 
                                                            </div>
                                                          </td>
                                                          <td>Cooling Schedule 1:</td>
                                                          <td><input type="text" name="cooling-schedule" style="width:50px" value="0:00" disabled></input></td>
                                                          <td><input type="text" name="cooling-schedule" style="width:50px" value="07:00" disabled></input></td>

                                                          <td>&nbsp;</td>

                                                          <td>Cooling Set Point Temperature 1: </td>
                                                          <td>
                                                            <div class="input-append">
                                                                <input class="span2" id="cooling-set-point" type="number" value="91" style="width:50px" disabled><span class="add-on">&deg;F</span>                                 
                                                            </div>
                                                          </td>
                                                          <td>Cooling Schedule 1:</td>
                                                          <td><input type="text" name="cooling-schedule" style="width:50px" value="0:00" disabled></input></td>
                                                          <td><input type="text" name="cooling-schedule" style="width:50px" value="07:00" disabled></input></td>
                                                        </tr>

                                                        <tr>
                                                          <td>Cooling Set Point Temperature 2: </td>
                                                          <td>
                                                            <div class="input-append">
                                                                <input class="span2" id="cooling-set-point" type="number" value="75" style="width:50px" disabled><span class="add-on">&deg;F</span>                                 
                                                            </div>
                                                          </td>
                                                          <td>Cooling Schedule 2:</td>
                                                          <td><input type="text" name="cooling-schedule" style="width:50px" value="07:00" disabled></input></td>
                                                          <td><input type="text" name="cooling-schedule" style="width:50px" value="19:00" disabled></input></td>

                                                          <td>&nbsp;</td>

                                                          <td>Cooling Set Point Temperature 2: </td>
                                                          <td>
                                                            <div class="input-append">
                                                                <input class="span2" id="cooling-set-point" type="number" value="75" style="width:50px" disabled><span class="add-on">&deg;F</span>                                 
                                                            </div>
                                                          </td>
                                                          <td>Cooling Schedule 2:</td>
                                                          <td><input type="text" name="cooling-schedule" style="width:50px" value="07:00" disabled></input></td>
                                                          <td><input type="text" name="cooling-schedule" style="width:50px" value="19:00" disabled></input></td>
                                                        </tr>


                                                        <tr>
                                                          <td>Cooling Set Point Temperature 3: </td>
                                                          <td>
                                                            <div class="input-append">
                                                                <input class="span2" id="cooling-set-point" type="number" value="75" style="width:50px" disabled><span class="add-on">&deg;F</span>                                 
                                                            </div>
                                                          </td>
                                                          <td>Cooling Schedule 3:</td>
                                                          <td><input type="text" name="cooling-schedule" style="width:50px" value="19:00" disabled></input></td>
                                                          <td><input type="text" name="cooling-schedule" style="width:50px" value="24:00" disabled></input></td>

                                                          <td>&nbsp;</td>

                                                          <td>Cooling Set Point Temperature 3: </td>
                                                          <td>
                                                            <div class="input-append">
                                                                <input class="span2" id="cooling-set-point" type="number" value="91" style="width:50px" disabled><span class="add-on">&deg;F</span>                                 
                                                            </div>
                                                          </td>
                                                          <td>Cooling Schedule 3:</td>
                                                          <td><input type="text" name="cooling-schedule" style="width:50px" value="19:00" disabled></input></td>
                                                          <td><input type="text" name="cooling-schedule" style="width:50px" value="24:00" disabled></input></td>
                                                        </tr>
                                                      </tbody>
                                                    </table>

                                                    <hr></br></br>

                                                    <table border="0" cellpadding="5" style="font-size: 11px;"> 
                                                      <tbody>
                                                        <tr>
                                                          <td></td>  
                                                          <td></td> 
                                                          <td></td>     
                                                          <td></td>  
                                                          <td></td> 
                                                          <td></td>                                                      
                                                          <td></td>  
                                                          <td></td> 
                                                          <td style="width:480px"></td> 
                                                          <td>Measure Cost:</td>
                                                          <td>
                                                            <div class="input-prepend input-append">
                                                              <span class="add-on">$</span>
                                                              <input type="text" name="measure" style="width:50px" value="" disabled></input>
                                                              <span class="add-on">.00</span>
                                                            </div>
                                                          </td>
                                                        </tr>

                                                        <tr>    
                                                          <td></td>  
                                                          <td></td> 
                                                          <td></td>     
                                                          <td></td>  
                                                          <td></td> 
                                                          <td></td>                                                      
                                                          <td></td>  
                                                          <td></td> 
                                                          <td></td> 
                                                          <td>Year of Installation:</td>
                                                          <td><input type="text" name="year" style="width:50px" value="" disabled></input></td>
                                                        </tr>
                                                      </tbody>
                                                    </table>
                                                    </br></br>  
                                                </div>
                                            </li>
                                        </ol>

                                    </li>
                                </ol>
                            </li>

                            <!-- Condensing Unit Replacement -->
                            <li>
                                <label for="system-efficiency">Condensing Unit Replacement</label> <input type="checkbox" id="system-efficiency"  name="selected[]" value="sysEffChecked"  /> 
                                <ol>
                                    <!-- Condensing Unit Replacement -->
                                    <li>
                                        <div>
                                            <br>
                                                <table border="0" cellpadding="5" style="font-size: 12px;"> 
                                                  <caption></caption>
                                                  <thead>
                                                    <tr>
                                                      <th style="font-size: 15px;">Current:</th>
                                                      <th></th>
                                                      <th style="width:180px"></th>
                                                      <th style="font-size: 15px;">Proposed:</th>
                                                      <th></th>
                                                    </tr>
                                                  </thead>

                                                  <tbody>
                                                    <tr>
                                                      <td align="center"><b>Low-Speed</b></td>
                                                      <td> &nbsp;</td>                           
                                                      <td>&nbsp;</td>
                                                      <td align="center"><b>Low-Speed</b></td>
                                                      <td>&nbsp;</td>
                                                    </tr>

                                                        <tr>
                                                      <td>Coefficient of Performance (COP): </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span2" id="current-cop" type="number" value="4.31" disabled>
                                                        </div> 
                                                      </td>
                                                     
                                                      <td>&nbsp;</td>

                                                      <td>Coefficient of Performance (COP):</td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span2" id="proposed-cop" type="number" value="5.37" disabled>                             
                                                        </div> 
                                                      </td>
                                                    </tr>

                                                    <tr>
                                                      <td align="center"><b>High-Speed</b></td>
                                                      <td> &nbsp;</td>                           
                                                      <td>&nbsp;</td>
                                                      <td align="center"><b>High-Speed</b></td>
                                                      <td>&nbsp;</td>
                                                    </tr>

                                                        <tr>
                                                      <td>Coefficient of Performance (COP): </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span2" id="current-cop" type="number" value="3.37" disabled>
                                                        </div> 
                                                      </td>
                                                     
                                                      <td>&nbsp;</td>

                                                      <td>Coefficient of Performance (COP):</td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span2" id="proposed-cop" type="number" value="4.37" disabled>                             
                                                        </div> 
                                                      </td>
                                                    </tr>


                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td></td> 
                                                      <td>Measure Cost:</td>
                                                      <td>
                                                        <div class="input-prepend input-append">
                                                          <span class="add-on">$</span>
                                                          <input type="text" name="measure" style="width:50px" value="" disabled></input>
                                                          <span class="add-on">.00</span>
                                                        </div>
                                                      </td>
                                                    </tr>

                                                    <tr>    
                                                      <td></td>                                                      
                                                      <td></td>  
                                                      <td></td> 
                                                      <td>Year of Installation:</td>
                                                      <td><input type="text" name="year" style="width:50px" value="" disabled></input></td>
                                                    </tr>

                                                  </tbody>
                                                </table>
                                            </br></br>
                                        </div>
                                        <br>
                                    </li>
                                </ol>
                            </li>  <!-- here -->





                        </ol>
                    </li>

                    <!-- Ventilation -->
                    <li>
                        <label for="ventilation">Ventilation</label> <input type="checkbox" id="ventilation" name="selected[]" value="ventilationHVACChecked"/> 
                        <ol>
                            <!-- Outdoor Air Economizer -->
                            <li>
                                <label for="outdoor-air-economizer">Outdoor Air Economizer</label> <input disabled type="checkbox" id="outdoor-air-economizer" name="selected[]" value="airEconChecked"/> 
                                <ol>            
                                    <li>
                                        <div>
                                            <br>
                                                <table border="0" cellpadding="5" style="font-size: 12px;"> 
                                                  <caption></caption>
                                                  <thead>
                                                    <tr>
                                                      <th style="font-size: 15px;">Current:</th>
                                                      <th></th>
                                                      <th style="width:150px"></th>
                                                      <th></th>
                                                      <th style="font-size: 15px;">Proposed:</th>
                                                      <th></th>
                                                      <th></th>
                                                    </tr>
                                                  </thead>

                                                  <tbody>
                                                    <tr>
                                                      <td>Outdoor Air Type Economizer: </td>
                                                      <td>
                                                        <select name="" disabled>
                                                            <option>No Economizer</option>
                                                            <option>Fixed Dry Bulb Temperature</option>
                                                        </select> 
                                                      </td>
                                                      <td><button class="btn">Select</button> </td>

                                                      <td>&nbsp;</td>
                                                      <td>Outdoor Air Type Economizer: </td>

                                                      <td>
                                                        <select name="" disabled>
                                                            <option>No Economizer</option>
                                                            <option selected>Fixed Dry Bulb Temperature</option>
                                                        </select> 
                                                      </td>

                                                      <td><button class="btn">Select</button> </td>
                                                    </tr>

                                                    <tr>
                                                      <td>Maximum Limit Temperature:  </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span1" id="plug-loan-density" type="number" disabled><span class="add-on">&deg;F</span>                                   
                                                        </div> 
                                                      </td>
                                                      <td>&nbsp;</td>
                                                      <td>&nbsp;</td>

                                                      <td>Maximum Limit Temperature:  </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span1" id="plug-loan-density" type="number" value="82.4" disabled><span class="add-on">&deg;F</span>                                  
                                                        </div> 
                                                      </td>
                                                      <td>&nbsp;</td>
                                                    </tr>

                                                    <tr>
                                                      <td></td>
                                                      <td> </td>
                                                      <td>&nbsp;</td>
                                                      <td>&nbsp;</td>

                                                      <td>Maximum Outdoor Air Flow Rate: </td>
                                                      <td>
                                                        <div class="input-append">
                                                            <input class="span2" id="air-flow-rate" type="number" value="14127" disabled><span class="add-on">CFM</span>                                    
                                                        </div> 
                                                      </td>
                                                      <td>&nbsp;</td>
                                                    </tr>

                                                    <tr>                                                         
                                                      <td></td>  
                                                      <td></td> 
                                                      <td></td> 
                                                      <td></td> 
                                                      <td>Measure Cost:</td>
                                                      <td>
                                                        <div class="input-prepend input-append">
                                                          <span class="add-on">$</span>
                                                          <input type="text" name="measure" style="width:50px" value="" disabled></input>
                                                          <span class="add-on">.00</span>
                                                        </div>
                                                      </td>
                                                       <td></td>
                                                    </tr>

                                                    <tr>    
                                                      <td></td>  
                                                      <td></td>                                                      
                                                      <td></td>  
                                                      <td></td> 
                                                      <td>Year of Installation:</td>
                                                      <td><input type="text" name="year" style="width:50px" value="" disabled></input></td>
                                                        <td>&nbsp;</td>
                                                    </tr>
                                                  </tbody>
                                                </table>
                                            </br></br>
                                        </div>
                                        <br>
                                            
                                    </li>
                                </ol>
                            </li>

                            <!-- Demand Control Ventilation -->
                        <!--    <li>
                                <label for="demand-control-ventilation">Demand Control Ventilation</label> <input type="checkbox" id="demand-control-ventilation" /> 
                                <ol>
                                    <li class="file"><a href="">Subfile 1</a></li>
                                </ol>
                            </li>
                        -->
                            <!-- Energy Recovery -->
                        <!--    <li>
                                <label for="energy-recovery">Energy Recovery</label> <input type="checkbox" id="energy-recovery" name="selected[]" value="energyRecChecked" /> 
                                <ol>
                                    <li class="file"><a href="">Subfile 1</a></li>
                                </ol>
                            </li>
                        -->
                        </ol>
                    </li>
                </ol>
            </li>

        </ol>
            <!--input type="submit" value="SIMULATE" onclick="this.form.submit();" /-->
<button type="submit" value="go to tracking sheet" formaction="http://rmt.eebhub.org/comprehensive/tracking-sheet.php" formmethod="post" id="restart-game-button" style="">Save Inputs & Perform EEM Staging</button>

            </br></br>
        </form>
        </div>
  <!-- ################################ Loading Bar ##################################################### -->
            <div class="loading-div container-fluid">
                <div id="filter" > 
                </div>
            
                <div class="progress progress-striped active">
                  <div class="bar" ></div>
                </div>
            
                <h4 id="loading-status">Simulation In Progress ...</h4>
            </div>

        <!-- proposed field -->
    <!--    <div id="Proposed-Field">       
            <h2>Proposed:</h2>
            <select>
                <option>Package 1</option>
                <option>Package 2</option>
                <option>Package 3</option>
            </select> 
            <button class="btn">Save to package</button>
            <button class="btn">Apply to all packages</button>  -->



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
    <script type="text/javascript" src="js/bootstrap-switch.min.js"></script>
    
    <script>
        // update loading bar
        function loading() {
            
            // current progress number in pixel
            var num = 0;
            
            // update the progress every second
            var update = setInterval(function(){
                
                // update the loading-status (text)
                $("#loading-status").html("Simulation In Progress "+parseInt(num/$(".loading-div").width()*100)+"%");
                
                // loading to the next process
                if(num>=$(".loading-div").width()) { 
                        $("#loading-status").html("Loading...");
                        clearInterval(update);
                }
                
                // update the loading bar 
                $(".bar").width(num);
                
                // increase the loading bar 3 pixes per second.
                num=num+3;
            },1000);
        }
    </script>
  </body>
</html>
