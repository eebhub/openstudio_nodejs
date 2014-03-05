var timestp = require("../lib/timestamp.js");
var fs = require("fs");

module.exports = {
    getHome: function(request, response){

        response.render('comprehensive');
    },
    
    getForm: function(request, response){
        response.render('form');
    },

    getEnergyUse: function(request, response){

        response.render('energy-use');
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

    getWalls: function(request, response){
        response.render('walls');
    }
    
};    