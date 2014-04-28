Run OpenStudio Ruby Measures from Node.js
-----------------------------------------

Energy Efficiency Measures (EEM)
-------------------------
1. [Reduce Lighting Loads by Percentage](https://bcl.nrel.gov/node/37875), ([code](https://github.com/eebhub/openstudio_nodejs/tree/develop/jason/measures/ReduceLightingLoadsByPercentage))
2. [Set Window to Wall Ratio by Facade](https://bcl.nrel.gov/node/37880), ([code](https://github.com/eebhub/openstudio_nodejs/tree/develop/jason/measures/SetWindowToWallRatioByFacade))
3. [Add Output Variable](https://bcl.nrel.gov/node/37843), ([code](https://github.com/eebhub/openstudio_nodejs/tree/develop/jason/measures/AddOutputVariable))

Add EEM to OpenStudio Model
---------------------------
The main logic for adding energy efficiency measures with OpenStudio API Node.js bindings is here:
>[openstudio-model.js#L380-L462](https://github.com/eebhub/openstudio_nodejs/blob/develop/jason/openstudio-model.js#L380-L462)


Test
------
From this current directory, in command line, run:

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