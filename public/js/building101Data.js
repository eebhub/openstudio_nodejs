//FILLS IN FORMS WITH BUILDING 101 DATA

//HTML
//<script src="js/building101Data.js"></script>
//<form action="action" method="post" name="platformForm">
//<button type="button"  onclick="building101Data()"> + Building 101</button>

//UNITS
//https://docs.google.com/spreadsheet/ccc?key=0AmpmAq6B1uv_dGNka3Q1LWVzaW5ObnNtbW9ZRkUwQ2c#gid=6

function building101Data_comprehensive() {

var form = document.forms.platformForm;
var address = document.getElementById('pac-input');

//BUILDING
if (form.contains(form.buildingName)) {form.buildingName.value = "Building 101";}
if (form.contains(form.weather)) {form.weather.value = "USA_PA_Philadelphia.Intl.AP.724080_TMY3";}
if (form.contains(form.activityType)) {form.activityType.value = "MediumOffice";}
if (form.contains(form.yearCompleted)) {form.yearCompleted.value = "1911";}

//Address from Google Maps
address.value = "4747 South Broad Street, Philadelphia, PA, United States";

//ARCHITECTURE
if (form.contains(form.footprintShape)) {form.footprintShape.value = "Rectangle";}
if (form.contains(form.length)) {form.length.value = 90;} 
if (form.contains(form.width)) {form.width.value = 30;}
if (form.contains(form.numberOfFloors)) {form.numberOfFloors.value = 4;} //conditioned space, includes basement
if (form.contains(form.floorToFloorHeight)) {form.floorToFloorHeight.value = 3.66;} 
if (form.contains(form.degreeToNorth)) {form.degreeToNorth.value = 275;} //clockwise from North, degrees
if (form.contains(form.windowToWallRatio)) {form.windowToWallRatio.value = 15;}

//MATERIALS
if (form.contains(form.windowConstruction)) {form.windowConstruction.value = "0.80";} //Multi-layer Glass, Windows Double Glazed, Double Clear
if (form.contains(form.wallConstruction)) {form.wallConstruction.value = "Concrete Mass";}
if (form.contains(form.roofConstruction)) {form.roofConstruction.value = "Attic and Other";}

//MECHANICAL
if (form.contains(form.primaryHvacType)) {form.primaryHvacType.value = "SystemType5";}
if (form.contains(form.fanEfficiency)) {form.fanEfficiency.value = 50;}
if (form.contains(form.boilerEfficiency)) {form.boilerEfficiency.value = 66;}
if (form.contains(form.coilCoolRatedHighSpeedCOP)) {form.coilCoolRatedHighSpeedCOP.value = 5.5;}
if (form.contains(form.coilCoolRatedLowSpeedCOP)) {form.coilCoolRatedLowSpeedCOP.value = 6.6;}
if (form.contains(form.economizerType)) {form.economizerType.value = "No Economizer";}
if (form.contains(form.boilerFuelType)) {form.boilerFuelType.value = "NaturalGas";}
if (form.contains(form.heatingSetpoint)) {form.heatingSetpoint.value = 24;}
if (form.contains(form.coolingSetpoint)) {form.coolingSetpoint.value = 26;}

}