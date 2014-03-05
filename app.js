
/**
 * Module dependencies.
 */
 var Tail, app, connect, fileName, io, socketio, tail;

connect = require('connect');

socketio = require('socket.io');

Tail = require('tail').Tail;

var fs = require('fs');

var express = require('express');
var http = require('http');
var path = require('path');
var routes = require('./routes/routes.js');
var openstudio = require('./routes/openstudio.js');

var app = express()
, server = require('http').createServer(app)
  , io = socketio.listen(server);

  server.listen(9099);

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


// development only
if ('development' == app.get('env')) {
  app.use(express.errorHandler());
}

app.get('/', routes.getHome);
app.get('/form', routes.getForm);
app.get('/energy-use.html', routes.getEnergyUse);
app.get('/energy-intensity.html', routes.getEnergyIntensity);
app.get('/energy-cost.html', routes.getEnergyCost);
app.get('/zone-component-load.html', routes.getZoneLoads);
app.get('/measure-list.html', routes.getMeasureList);
app.get('/tracking-sheet.html', routes.getTrackingSheet);
app.get('/walls.ejs', routes.getWalls);


app.get('/eplus_out', function(req, res){

/*test simple selections*/
//   var sqlite3 = require('sqlite3').verbose();
//   var db = new sqlite3.Database('test/eem_1.sql');
// var str = '';
// db.serialize(function() {

// db.each("SELECT * FROM Surfaces", function(err, row){
//     str = row.SurfaceIndex + ',' + row.SurfaceName + ',' + row.Area + '\n';
//   console.log(str);
  
// });
// });
// db.close(); 


/*test getValue*/
// var sqlite3= require('./lib/eeb_sqlite3.js');
// sqlite3.getValues('ENVELOPE%', 'ENTIRE%', 'Opaque Exterior', 'Btu%', 'test/eem_1.sql', function(results){
//     console.log(results);
// });

/*test getReportForStrings*/
// var sqlite3= require('./lib/eeb_sqlite3.js');
// sqlite3.getReportForStrings('ShadingSummary', 'test/eem_1.sql', function(results){
//     console.log(results);
// });

/*test getValuesByMonthly*/

var sqlite3 = require('./lib/eeb_sqlite3.js');
sqlite3.getValuesByMonthly('ENVELOPE%', 'ENTIRE%', 'Opaque Exterior', 'Btu%', 'test/eem_1.sql', function(results){
    console.log(results);
});

});

app.post('/rmt', openstudio.simulateOpenstudio);

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

    var path = "http://128.118.67.241/openstudio/outputs/ENERGYPLUS/idf/Simulation_"+value+".idf/EnergyPlusPreProcess/EnergyPlus-0/stdout";

    if (fs.existsSync(path)) 
    {
        tail = new Tail(path);
        console.log("**********" + value);
  
        tail.on('line', function(data) {
          return io.sockets.emit('new-data', {
          channel: 'stdout',
          value: data
          });
        });
    }
    else
    {
      console.log("file not found");
    }
	
});

  return socket.emit('new-data', {
    channel: 'stdout',
    value: ""
  });
});
