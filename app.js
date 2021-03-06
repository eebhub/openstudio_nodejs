/**
 * Module dependencies.
 */
findStdOut = 'before';				// global variable for simulationID

var  fileName, io, socketio, tail;
var connect = require('connect');
var socketio = require('socket.io');
var Tail = require('tail').Tail;
var fs = require('fs');
var express = require('express');
var http = require('http');
var path = require('path');
var routes = require('./routes/routes.js');
var testOpenstudio = require('./archive/testOpenstudio.js');


var app = express()
, server = require('http').createServer(app)
  , io = socketio.listen(server);

  server.listen(9099);

//module.exports.io = io;


// all environments
app.set('port', process.env.PORT || 3000);
app.set('views', __dirname + '/views');
app.set('view engine', 'ejs');
app.use(express.favicon());
app.use(express.logger('dev'));
app.use(express.bodyParser());
app.use(express.methodOverride());
app.use(app.router);
app.use(express.static(path.join(__dirname, 'public')));



//Show Folders & Files like Apache
express.static.mime.define({'text/plain': ['idf', 'osm', 'epw', 'err', 'idd', 'eio','audit','bnd','end', 'eso','mdd','mtd','mtr','rdd','shd']});
express.static.mime.default_type = "text/plain"; //to render files without an extention, i.e. stdout, stderr
app.use(express.directory('public'));
app.use('/simulations', express.directory('../simulations', {icons:true}));
app.use('/simulations', express.static('../simulations'));


// development only
if ('development' == app.get('env')) {
  app.use(express.errorHandler());
}

app.get('/', routes.getHome);
app.get('/form', routes.getForm);
app.get('/measure-list.html', routes.getMeasureList);
app.get('/tracking-sheet.html', routes.getTrackingSheet);
app.get('/data-structure', routes.getDataStructure);
app.get('/data', routes.getDataStructure);
app.get('/json', routes.getDataStructure);
app.get('/walls', routes.getWalls);
app.get('/tracking', routes.getTracking);
app.get('/output', routes.testOutput);
app.get('/outputs', routes.testOutput);

app.get("/presentation", function(req, res) {
    res.redirect("http://developer.eebhub.org/archives/presentations/OpenStudio-Node.js-to-DOE-NREL-4.30.14.pdf");
});

//Simulate OpenStudio & EnergyPlus
//console.log("*********BEFORE");
var simulate = require("./routes/simulate.js");
app.post('/simulate', simulate.openstudio);
//console.log("*********After");


app.post('/rmt', testOpenstudio.simulateOpenstudio);

http.createServer(app).listen(app.get('port'), function(){
  console.log('Express server listening on port ' + app.get('port'));
});

io.sockets.on('connection', function(socket) {
  console.log("CONNECT!");

  socket.on('room1', function(value){
  console.log("****room1: " + value + "%");
  });

  socket.on('room2', function(value){
  console.log("****room2: " + value + "%");
  });

  socket.on('room3', function(value){
  console.log("****room3: " + value + "%");
  });

  socket.on('room4', function(value){
  console.log("****room4: " + value + "%");
  });


  socket.on('randomNumber', function(value){

    //console.log("#################################EMITTED");
    
    var outputFilePath = findStdOut + "1-EnergyPlus-0/stdout"; 
    //console.log('###$$$###$$$###$$' + outputFilePath);
    
   
	
    if (fs.existsSync(outputFilePath))
    {
	//console.log('###$$$###$$$###$$ FILE FOUND ');
        tail = new Tail(outputFilePath);


	io.sockets.emit('redirectPath', {
		channel: 'stdout',
		value: findStdOut});

        tail.on('line', function(data) {
          return io.sockets.emit('new-data', {
          channel: 'stdout',
          value: data
          });
        });
    }
    else
    {
	io.sockets.emit('fileNotFound', {
		channel: 'stdout',
		value: 'Stdout file not found'});
     // console.log("file not found");
    }

});

  return socket.emit('new-data', {
    channel: 'stdout',
    value: ""
  });
});
