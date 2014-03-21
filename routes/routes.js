var timestp = require("../library/timestamp.js");
var fs = require("fs");
var sqlToJSON = require("../library/outputs.js").sqlToJSON;

module.exports = {
    getHome: function(request, response){

        response.render('comprehensive');
    },

    getForm: function(request, response){
        response.render('form');
    },
    getTracking:function(request, response){
        response.render('tracking-sheet');
    },
    getEnergyIntensity: function(request, response){
        response.render('energy-intensity');
    },

    getEnergyCost: function(request, response){

        response.render('energy-cost');
    },

    getZoneLoads: function(request, response){
        response.render('zone-component-load');
    },

    getMeasureList: function(request, response){

        response.render('measure-list');
    },

    getTrackingSheet: function(request, response){
        response.render('tracking-sheet');
    },

    getDataStructure: function(request, response){
        response.render('data-structure');
    },

    getWalls: function(request, response){
        response.render('walls');
    },
    testOutput: function(request, response){
      var database = "test/eem_1.sql";
      sqlToJSON(database, function(ePlusOutputs){
          console.log(ePlusOutputs.energyUse.naturalGas);
          response.render('output', {
              output: ePlusOutputs
          })
      });

    }

};
