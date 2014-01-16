var timestp = require("../lib/timestamp.js");
var fs = require("fs");

module.exports = {
    getHome: function(request, response){

        response.render('index');
    },
    
    getForm: function(request, response){
        response.render('form');
    }
    
};    