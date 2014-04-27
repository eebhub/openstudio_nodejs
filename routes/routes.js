var timestp = require("../library/timestamp.js");
var fs = require("fs");
var sqlToJSON = require("../library/sqlToJSON.js").sqlToJSON;

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
          console.log(ePlusOutputs.area);
          response.render('output', {
              output: ePlusOutputs
          })
      });

    }

};
