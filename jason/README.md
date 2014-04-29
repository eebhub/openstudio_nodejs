Run OpenStudio Ruby Measures from Node.js
-----------------------------------------
[NREL's Building Component Library](https://bcl.nrel.gov/nrel/types/measure) is a growing database of measures that make modeling building energy efficiency with OpenStudio much easier.  Today, the measures are only coded in Ruby.  Documentation and code logic below shows how to call OpenStudio Ruby Measures from Node.js.  This will make it possible to use the measures as off-the-shelf code and build your fully JavaScript web application around it.

Energy Efficiency Measures
-------------------------
For this project, we focused on 3 measures:

 1. [Reduce Lighting Loads by Percentage](https://bcl.nrel.gov/node/37875), ([code](https://github.com/eebhub/openstudio_nodejs/tree/develop/jason/measures/ReduceLightingLoadsByPercentage))
 2. [Set Window to Wall Ratio by Facade](https://bcl.nrel.gov/node/37880), ([code](https://github.com/eebhub/openstudio_nodejs/tree/develop/jason/measures/SetWindowToWallRatioByFacade))
 3. [Add Output Variable](https://bcl.nrel.gov/node/37843), ([code](https://github.com/eebhub/openstudio_nodejs/tree/develop/jason/measures/AddOutputVariable))

Add Measure to OpenStudio Model
---------------------------
The main logic for adding energy efficiency measures with OpenStudio API Node.js bindings is here:
>[openstudio-model.js#L380-L462](https://github.com/eebhub/openstudio_nodejs/blob/develop/jason/openstudio-model.js#L380-L462)


Test
------
From this current 'jason' directory, in command line, run:

```sh
node openstudio-run.js
```

This will simulate the model default described in [buildingData.json](https://github.com/eebhub/openstudio_nodejs/blob/develop/jason/buildingData.json).

Example
-------
Before:
* [WITHOUT Measures](http://node.eebhub.org/simulations/JASON_2014-04-27_21.46.37.594/2-EnergyPlus-0/eplustbl.htm) = 98.34 kBTU/ft2

After:
* [WITH Measures](http://node.eebhub.org/simulations/JASON_2014-04-27_21.25.38.626/5-EnergyPlus-0/eplustbl.htm) = 93.67 kBTU/ft2