<?php

class EEB_UI {

	function drawNavbar() {
		echo '
			<div class="navbar navbar-inverse navbar-fixed-top">
		    <div class="navbar-inner">
		    <div class="container-fluid" >
		      <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
		        <span class="icon-bar"></span>
		        <span class="icon-bar"></span>
		        <span class="icon-bar"></span>
		      </button>
		      <a class="brand pull-left" href="http://tools.eebhub.org/">EEB Hub Simulation Platform</a>
		      <div class="nav-collapse collapse">
		        <ul class="nav">
		          <li class="">
		            <a href="http://tools.eebhub.org/lite">LITE</a>
		          </li>
		          <li class="">
		            <a href="http://tools.eebhub.org/partial">PARTIAL</a>
		          </li>
		          <li class="">
		            <a href="http://tools.eebhub.org/substantial">SUBSTANTIAL</a>
		          </li>
		          <li class="active">
		            <a href="http://tools.eebhub.org/comprehensive">COMPREHENSIVE</a>
		          </li>
		        </ul>
		      </div>
		    </div>
		  	</div>
			</div>';
	}

	function drawSubNavbar($active) {
        
		echo '
			<!-- Sub-Nav-bar -->
			<div class="navbar">
			  <div class="navbar-inner">
			    <a class="brand" href="#">Open Studio SDK And Energy Plus</a>
			    <ul class="nav">
			      <li class="'.$active[energy].'"><a href="./energy-use.php">Energy</a></li>
				  <li class="'.$active[intensity].'"><a href="./energy-intensity.php">Energy Intensity</a></li>
			      <li class="'.$active[zone].'"><a href="./zone-component-load.php">Zone Loads</a></li>
			      <li class="'.$active[cost].'"><a href="./energy-cost.php">Energy Cost</a></li>
			      <li class="'.$active[comparison].'"><a href="./all-site-energy.php">EEM Comparison</a></li>
			      <li class=""><a href="./tracking-sheet.php">Tracking Sheet</a></li>
			    </ul>
			  </div>
			</div>';
	}

	function drawSwitchPackage(){
		
  		echo '    
			<form action="" method="post">
				<div style="font-size: 25px; font-weight: bold; color: #eee; margin-top: 20px;">
				<select id="num_package" name="num_package" onchange="this.form.submit();">';

            echo "<option value=\"$cur_model\">$_SESSION[cur_model]</option>";
          foreach( $_SESSION['Model'] as $eem_model ) {
            echo "<option value=\"$eem_model\">$eem_model</option>";
          }

               echo '				</select>';
				// echo '<button type="submit" value="go to measure list" formaction="measure-list.php" formmethod="post" id="measure-list-button" style="">
				// 	EEM Selection
				// </button>';
				echo '</div>
			</form>';
    }
}  // END of EEB_UI

?>
