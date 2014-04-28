Run OpenStudio with Node.js
---------------------------

OpenStudio/EnergyPlus can be run from Node.js (JavaScript) bindings with just 3 files:

1. **Input**: [buildingdata2.json](https://github.com/eebhub/openstudio_nodejs/blob/develop/library/buildingData2.json)
2. **Run**: [openstudio-run-solo.js](https://github.com/eebhub/openstudio_nodejs/blob/develop/library/openstudio-run-solo.js)
3. **Model**: [openstudio-model.js](https://github.com/eebhub/openstudio_nodejs/blob/develop/library/openstudio-model.js)

Test
----
From terminal:

1. `git clone https://github.com/eebhub/openstudio_nodejs.git`

2. `cd openstudio_nodejs/library/`

3. From this current directory, run:

    ```sh
    node openstudio-run-solo.js
    ```

This will simulate the model default described in [buildingData2.json].

[buildingData2.json]:https://github.com/eebhub/openstudio_nodejs/blob/develop/library/buildingData2.json

*Note: Other files in this folder are required to run simulations from http://node.eebhub.org.*